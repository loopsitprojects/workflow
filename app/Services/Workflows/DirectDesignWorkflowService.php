<?php

namespace App\Services\Workflows;

use App\Models\Deliverable;
use App\Models\User;
use App\Notifications\DeliverableUpdated;
use App\Services\Workflows\Traits\HandlesWorkflowUploads;
use Illuminate\Support\Facades\Log;

/**
 * DirectDesignWorkflowService
 *
 * Implements the lean Manager -> Designer -> Manager Approval -> Closed workflow:
 * 1. Designer (In Design / Artwork Upload)
 * 2. Manager Review (Approval or Revisions)
 * 3. Closed (Finalized)
 *
 * Completely isolated from RetainerWorkflowService, CampaignWorkflowService,
 * and OtherDeliverableWorkflowService.
 */
class DirectDesignWorkflowService implements WorkflowInterface
{
    use HandlesWorkflowUploads;

    public const STAGES = [
        'Designer',
        'Manager Review',
        'Closed'
    ];

    /**
     * Normalize stage names (handles aliases).
     */
    public function normalizeStage(?string $stage): string
    {
        if (!$stage) return self::STAGES[0];
        $lower = strtolower(trim($stage));
        if (in_array($lower, ['designer', 'design', 'in design', 'artwork'])) return 'Designer';
        if (in_array($lower, ['manager review', 'review', 'approve', 'manager approval', 'am/bd', 'final approval'])) return 'Manager Review';
        if (in_array($lower, ['closed', 'close', 'done'])) return 'Closed';
        return $stage;
    }

    /**
     * Get the ordered stages for this workflow.
     */
    public function getStages(): array
    {
        return self::STAGES;
    }

    /**
     * Calculate stage progress percentage.
     * Designer: 20%, Manager Review: 60%, Closed: 100%
     */
    public function getStageProgress(Deliverable $deliverable): int
    {
        $stage = $this->normalizeStage($deliverable->approval_stage);
        return match ($stage) {
            'Designer'       => 20,
            'Manager Review' => 60,
            'Closed'         => 100,
            default          => 20,
        };
    }

    /**
     * Determine next stage in workflow.
     */
    public function getNextStage(Deliverable $deliverable, array $data = []): ?string
    {
        $currentStage = $this->normalizeStage($deliverable->approval_stage);
        return match ($currentStage) {
            'Designer'       => 'Manager Review',
            'Manager Review' => 'Closed',
            default          => null,
        };
    }

    /**
     * Determine previous stage in workflow.
     */
    public function getPrevStage(Deliverable $deliverable): ?string
    {
        $currentStage = $this->normalizeStage($deliverable->approval_stage);
        return match ($currentStage) {
            'Closed'         => 'Manager Review',
            'Manager Review' => 'Designer',
            default          => null,
        };
    }

    /**
     * Get the database column representing the user responsible for a stage.
     */
    public function getRequiredFieldForStage(string $stage): ?string
    {
        $norm = $this->normalizeStage($stage);
        return match ($norm) {
            'Designer'       => 'designer_id',
            'Manager Review' => 'brand_manager_id',
            default          => null,
        };
    }

    /**
     * Get user who should be notified for a specific stage.
     */
    public function getNotifyTarget(Deliverable $deliverable, string $stage): ?User
    {
        $norm = $this->normalizeStage($stage);
        $target = match ($norm) {
            'Designer'       => $deliverable->designer ?? ($deliverable->designer_id ? User::find($deliverable->designer_id) : null),
            'Manager Review' => $deliverable->brandManager ?? $deliverable->project?->brandManager ?? $deliverable->project?->lead,
            'Closed'         => $deliverable->designer ?? $deliverable->brandManager,
            default          => null,
        };

        if (!$target && $deliverable->project) {
            $target = $deliverable->project->lead;
        }
        if (!$target) {
            $target = User::where('role', 'Admin')->first();
        }
        return $target;
    }

    /**
     * Validate prerequisites for advancing a Direct Design Deliverable.
     */
    public function validateAdvance(Deliverable $deliverable, array $data, ?User $user): ?array
    {
        $nextStage = $this->getNextStage($deliverable, $data);

        if (!$nextStage) {
            return ['success' => false, 'message' => 'Deliverable is already at the final stage.', 'code' => 400];
        }

        $oldStage = $this->normalizeStage($deliverable->approval_stage);

        // Role authorization check
        if ($oldStage === 'Designer') {
            $userRole = strtolower(str_replace(' ', '', $user->role ?? ''));
            $isAssignedDesigner = $deliverable->designer_id && $user && $user->id == $deliverable->designer_id;
            $isUnassignedDesigner = !$deliverable->designer_id && $userRole === 'designer';
            $isPrivileged = $user && ($user->isAdmin() || in_array($userRole, ['operationsmanager', 'brandmanager']));

            if (!$isAssignedDesigner && !$isUnassignedDesigner && !$isPrivileged) {
                return [
                    'success' => false,
                    'message' => 'Direct design deliverables in Designer stage can only be submitted by the assigned designer.',
                    'code'    => 403,
                ];
            }
        } elseif ($oldStage === 'Manager Review') {
            $userRole = strtolower(str_replace(' ', '', $user->role ?? ''));
            $isBrandManager = $deliverable->brand_manager_id && $user && $user->id == $deliverable->brand_manager_id;
            $isPrivileged = $user && ($user->isAdmin() || in_array($userRole, ['brandmanager', 'operationsmanager']));

            if (!$isBrandManager && !$isPrivileged) {
                return [
                    'success' => false,
                    'message' => 'Only the Brand Manager can approve and close this deliverable.',
                    'code'    => 403,
                ];
            }
        }

        return null;
    }

    /**
     * Advance Direct Design Deliverable to next stage (Designer -> Manager Review -> Closed).
     */
    public function advanceStage(Deliverable $deliverable, array $data, ?User $user, bool $dryRun = false): array
    {
        $validationError = $this->validateAdvance($deliverable, $data, $user);
        if ($validationError !== null) {
            return $validationError;
        }

        $oldStage = $this->normalizeStage($deliverable->approval_stage);
        $nextStage = $this->getNextStage($deliverable, $data);

        $hoursSpent = isset($data['hours_spent']) && is_numeric($data['hours_spent']) && $data['hours_spent'] > 0
            ? (float) $data['hours_spent'] : null;

        if ($dryRun) {
            return ['success' => true];
        }

        // Auto-assign designer if not already set and acting user is designer
        if ($oldStage === 'Designer' && !$deliverable->designer_id && $user && strtolower(str_replace(' ', '', $user->role)) === 'designer') {
            $deliverable->designer_id = $user->id;
        }

        // Auto-assign manager if not set
        if (!$deliverable->brand_manager_id && $deliverable->project?->brand_manager_id) {
            $deliverable->brand_manager_id = $deliverable->project->brand_manager_id;
        }

        // Process file uploads if instances of UploadedFile
        if (isset($data['reference_file']) && $data['reference_file'] instanceof \Illuminate\Http\UploadedFile) {
            $deliverable->reference_file = $this->moveUploadedFile($data['reference_file'], 'references');
        } elseif (isset($data['reference_file'])) {
            $deliverable->reference_file = $data['reference_file'];
        }

        if (isset($data['final_designs']) && $data['final_designs'] instanceof \Illuminate\Http\UploadedFile) {
            $deliverable->final_designs = $this->moveUploadedFile($data['final_designs'], 'artwork');
        } elseif (isset($data['final_designs'])) {
            $deliverable->final_designs = $data['final_designs'];
        }

        // Content updates
        if (isset($data['title'])) $deliverable->title = $data['title'];
        if (isset($data['concept'])) $deliverable->concept = $data['concept'];
        if (isset($data['notes'])) $deliverable->notes = $data['notes'];
        if (isset($data['reference'])) $deliverable->reference = $data['reference'];
        if (isset($data['final_designs_link'])) $deliverable->final_designs_link = $data['final_designs_link'];
        if (isset($data['designer_id'])) $deliverable->designer_id = $data['designer_id'];
        if (isset($data['brand_manager_id'])) $deliverable->brand_manager_id = $data['brand_manager_id'];
        if (isset($data['designer_deadline'])) $deliverable->designer_deadline = $data['designer_deadline'];

        // Reset client status when advancing
        $deliverable->client_status = null;

        // Stage progression
        $deliverable->approval_stage = $nextStage;
        $deliverable->progress_percent = $this->getStageProgress($deliverable);
        $deliverable->revision_instructions = null;
        $deliverable->status = ($nextStage === 'Closed') ? 'Done' : 'To Do';
        $deliverable->is_ready = false;
        if ($hoursSpent) {
            $deliverable->work_hours = ($deliverable->work_hours ?? 0) + $hoursSpent;
        }
        $deliverable->save();

        // History
        $approvalData = [
            'user_id' => $user?->id ?? auth()->id(),
            'stage'   => $oldStage,
            'notes'   => $data['submit_notes'] ?? null
        ];
        if ($hoursSpent) $approvalData['hours_spent'] = $hoursSpent;
        $deliverable->approvalsHistory()->create($approvalData);
        $deliverable->revisionsHistory()->whereNull('fixed_by_user_id')->latest()->first()?->update([
            'fixed_by_user_id' => $user?->id ?? auth()->id(),
            'fixed_at'         => now()
        ]);

        // Notify
        $deliverable->notifyStageChange($oldStage, $nextStage, $user ?? auth()->user());

        $msg = match ($nextStage) {
            'Manager Review' => 'Artwork submitted and sent to Manager for review.',
            'Closed'         => 'Deliverable approved and closed.',
            default          => "Deliverable advanced to {$nextStage} stage.",
        };
        return ['success' => true, 'message' => $msg];
    }

    /**
     * Handle revision request in Direct Design workflow.
     * Routes back from Manager Review to Designer.
     */
    public function requestRevisions(Deliverable $deliverable, array $validatedData, ?string $imagePath, ?User $user): array
    {
        $oldStage = $this->normalizeStage($deliverable->approval_stage);

        if ($oldStage === 'Designer') {
            return ['success' => false, 'message' => 'Cannot request revisions for a deliverable already in Designer stage.', 'code' => 422];
        }

        // Reset back to Designer
        $deliverable->approval_stage = 'Designer';
        $deliverable->status = 'To Do';
        $deliverable->progress_percent = $this->getStageProgress($deliverable);
        $deliverable->revisions += 1;
        $deliverable->revision_instructions = $validatedData['revision_instructions'];
        $deliverable->save();

        // History
        $deliverable->revisionsHistory()->create([
            'user_id'           => $user?->id ?? auth()->id(),
            'instructions'      => $validatedData['revision_instructions'],
            'image_path'        => $imagePath,
            'stage_at_revision' => $oldStage,
        ]);

        // Notify Designer
        $notifyTarget = $deliverable->designer ?? ($deliverable->designer_id ? User::find($deliverable->designer_id) : null);
        if ($notifyTarget) {
            try {
                $notifyTarget->notify(new DeliverableUpdated(
                    $deliverable,
                    "requested revisions at stage **{$oldStage}**",
                    'revision_request',
                    $user ?? auth()->user()
                ));
            } catch (\Throwable $e) {
                Log::warning('Failed to send DeliverableUpdated notification: ' . $e->getMessage());
            }
        }

        return ['success' => true, 'message' => 'Revision requested and sent back to designer.'];
    }
}

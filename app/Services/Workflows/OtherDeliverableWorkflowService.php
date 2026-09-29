<?php

namespace App\Services\Workflows;

use App\Models\Deliverable;
use App\Models\User;
use App\Notifications\DeliverableUpdated;
use App\Services\Workflows\Traits\HandlesWorkflowUploads;
use Illuminate\Support\Facades\Log;

/**
 * OtherDeliverableWorkflowService
 *
 * Implements the lean 3-stage workflow for Other Deliverables (non-outlines)
 * in Campaign and Pitch projects:
 * Assign -> Approve -> Close
 *
 * Completely isolated from RetainerWorkflowService and CampaignWorkflowService.
 */
class OtherDeliverableWorkflowService implements WorkflowInterface
{
    use HandlesWorkflowUploads;

    public const STAGES = [
        'Assign',
        'Approve',
        'Close'
    ];

    /**
     * Normalize stage names (handles aliases like Assignee, Closed).
     */
    public function normalizeStage(?string $stage): string
    {
        if (!$stage) return self::STAGES[0];
        $lower = strtolower(trim($stage));
        if (in_array($lower, ['assign', 'assignee', 'writer'])) return 'Assign';
        if (in_array($lower, ['approve', 'approver', 'brand manager', 'am/bd', 'final approval'])) return 'Approve';
        if (in_array($lower, ['close', 'closed', 'done'])) return 'Close';
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
     * Assign: 10%, Approve: 50%, Close: 100%
     */
    public function getStageProgress(Deliverable $deliverable): int
    {
        $stage = $this->normalizeStage($deliverable->approval_stage);
        return match ($stage) {
            'Assign'  => 10,
            'Approve' => 50,
            'Close'   => 100,
            default   => 10,
        };
    }

    /**
     * Determine next stage in workflow.
     */
    public function getNextStage(Deliverable $deliverable, array $data = []): ?string
    {
        $currentStage = $this->normalizeStage($deliverable->approval_stage);
        return match ($currentStage) {
            'Assign'  => 'Approve',
            'Approve' => 'Close',
            default   => null,
        };
    }

    /**
     * Determine previous stage in workflow.
     */
    public function getPrevStage(Deliverable $deliverable): ?string
    {
        $currentStage = $this->normalizeStage($deliverable->approval_stage);
        return match ($currentStage) {
            'Close'   => 'Approve',
            'Approve' => 'Assign',
            default   => null,
        };
    }

    /**
     * Get the database column representing the user responsible for a stage.
     */
    public function getRequiredFieldForStage(string $stage): ?string
    {
        $norm = $this->normalizeStage($stage);
        return match ($norm) {
            'Assign'  => 'writer_id',
            'Approve' => 'brand_manager_id',
            default   => null,
        };
    }

    /**
     * Get user who should be notified for a specific stage.
     */
    public function getNotifyTarget(Deliverable $deliverable, string $stage): ?User
    {
        $norm = $this->normalizeStage($stage);
        $target = match ($norm) {
            'Approve'         => $deliverable->brandManager ?? $deliverable->project?->brandManager,
            'Assign', 'Close' => $deliverable->writer ?? $deliverable->project?->writer,
            default           => null,
        };

        if (!$target) {
            $target = $deliverable->project?->lead;
        }
        if (!$target) {
            $target = User::where('role', 'Admin')->first();
        }
        return $target;
    }

    /**
     * Validate prerequisites for advancing an Other Deliverable.
     */
    public function validateAdvance(Deliverable $deliverable, array $data, ?User $user): ?array
    {
        $nextStage = $this->getNextStage($deliverable, $data);

        if (!$nextStage) {
            return ['success' => false, 'message' => 'Deliverable is already at the final stage.', 'code' => 400];
        }

        $oldStage = $this->normalizeStage($deliverable->approval_stage);

        // Required field validation (only enforce if not resolvable from project/lead)
        $requiredField = $this->getRequiredFieldForStage($nextStage);
        if ($requiredField) {
            $assignedId = $data[$requiredField] ?? $deliverable->{$requiredField};
            if (!$assignedId && $deliverable->project) {
                $assignedId = $deliverable->project->{$requiredField};
            }

            if (!$assignedId && !$deliverable->project?->lead_id && !$user?->isAdmin()) {
                $roleName = ucwords(str_replace(['_id', '_'], ['', ' '], $requiredField));
                return [
                    'success' => false,
                    'message' => "Cannot move to **{$nextStage}**: Please assign a **{$roleName}** to this specific task first.",
                    'code' => 422
                ];
            }
        }

        // Role authorization check (non-admin)
        if ($user && !$user->isAdmin()) {
            if ($oldStage === 'Assign') {
                $isAssigned = ($deliverable->writer_id && $user->id == $deliverable->writer_id) ||
                              ($deliverable->designer_id && $user->id == $deliverable->designer_id);
                $isManager = in_array($user->role, ['Operations Manager', 'Brand Manager']);
                $hasAssigneeRole = in_array($user->role, ['Writer', 'Designer', 'Assignee']);
                if ((!$isManager && !$isAssigned && $deliverable->writer_id) ||
                    (!$isManager && !$deliverable->writer_id && !$hasAssigneeRole)) {
                    return [
                        'success' => false,
                        'message' => 'Only the assigned team member or manager can submit this deliverable for approval.',
                        'code'    => 403,
                    ];
                }
            } elseif ($oldStage === 'Approve') {
                if (!in_array($user->role, ['Brand Manager', 'Operations Manager'])) {
                    return [
                        'success' => false,
                        'message' => 'Only the Brand Manager can approve and close this deliverable.',
                        'code'    => 403,
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Advance Other Deliverable to next stage (Assign -> Approve -> Close).
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

        // Record who performed Assign stage if not already set
        if ($oldStage === 'Assign' && !$deliverable->writer_id) {
            $deliverable->writer_id = $user?->id ?? auth()->id();
            if ($user) $deliverable->assignee_name = $user->name;
        }

        // Content updates
        if (isset($data['title'])) $deliverable->title = $data['title'];
        if (isset($data['notes'])) $deliverable->notes = $data['notes'];
        if (isset($data['concept'])) $deliverable->concept = $data['concept'];
        if (isset($data['description'])) $deliverable->description = $data['description'];
        if (isset($data['reference'])) $deliverable->reference = $data['reference'];
        if (isset($data['reference_file'])) $deliverable->reference_file = $data['reference_file'];
        if (isset($data['final_designs'])) $deliverable->final_designs = $data['final_designs'];
        if (isset($data['final_designs_link'])) $deliverable->final_designs_link = $data['final_designs_link'];

        // Stakeholder updates
        if (isset($data['writer_id'])) $deliverable->writer_id = $data['writer_id'];
        if (isset($data['brand_manager_id'])) $deliverable->brand_manager_id = $data['brand_manager_id'];

        // Reset client status when advancing
        $deliverable->client_status = null;

        // Stage progression
        $deliverable->approval_stage = $nextStage;
        $deliverable->progress_percent = $this->getStageProgress($deliverable);
        $deliverable->revision_instructions = null;
        $deliverable->status = ($nextStage === 'Close') ? 'Done' : 'To Do';
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
            'Approve' => 'Deliverable sent to Brand Manager for approval.',
            'Close'   => 'Deliverable approved and closed.',
            default   => "Deliverable advanced to {$nextStage} stage.",
        };
        return ['success' => true, 'message' => $msg];
    }

    /**
     * Handle revision request in Other Deliverable workflow.
     * Routes back from Approve to Assign.
     */
    public function requestRevisions(Deliverable $deliverable, array $validatedData, ?string $imagePath, ?User $user): array
    {
        $oldStage = $this->normalizeStage($deliverable->approval_stage);

        if ($oldStage === 'Assign') {
            return ['success' => false, 'message' => 'Cannot request revisions for this stage.', 'code' => 422];
        }

        // Reset back to Assign
        $deliverable->approval_stage = 'Assign';
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

        // Notify Assignee
        $notifyTarget = $deliverable->writer ?? $deliverable->project?->writer;
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

        return ['success' => true, 'message' => 'Revision requested successfully.'];
    }
}

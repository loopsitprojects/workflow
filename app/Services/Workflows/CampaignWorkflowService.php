<?php

namespace App\Services\Workflows;

use App\Models\Deliverable;
use App\Models\User;
use App\Notifications\DeliverableUpdated;
use App\Services\Workflows\Traits\HandlesWorkflowUploads;
use Illuminate\Support\Facades\Log;

/**
 * CampaignWorkflowService
 *
 * Implements the 10-stage Outline pipeline for Campaign and Pitch projects:
 * Writer → Approver → Further Approver → Brand Manager → Coordinator → Designer → Writer Review → Approver Review → AM/BD → Final Approval → Closed
 *
 * Completely isolated from RetainerWorkflowService so changes here never affect retainer flows.
 */
class CampaignWorkflowService implements WorkflowInterface
{
    use HandlesWorkflowUploads;

    public const STAGES = [
        'Writer',
        'Approver',
        'Further Approver',
        'Brand Manager',
        'Coordinator',
        'Designer',
        'Writer Review',
        'Approver Review',
        'AM/BD',
        'Final Approval',
        'Closed'
    ];

    public const STEPPER_STAGES = [
        'Writer',
        'Approver',
        'Further Approver',
        'Brand Manager',
        'Coordinator',
        'Designer',
        'Writer Review',
        'Approver Review',
        'AM/BD',
        'Final Approval'
    ];

    /**
     * Get the ordered stages for campaign/pitch outline workflow.
     */
    public function getStages(): array
    {
        return self::STAGES;
    }

    /**
     * Calculate stage progress percentage for campaign/pitch outline workflow.
     */
    public function getStageProgress(Deliverable $deliverable): int
    {
        $stages = $this->getStages();
        $stage = $deliverable->approval_stage ?? $stages[0];
        if ($stage === 'Assignee') $stage = 'Writer';
        
        $index = array_search($stage, $stages);
        if ($index === false) return 0;
        
        $milestones = [0, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100];
        return $milestones[$index] ?? 0;
    }

    /**
     * Determine next stage in campaign outline workflow.
     */
    public function getNextStage(Deliverable $deliverable, array $data = []): ?string
    {
        $stages = $this->getStages();
        $stage = $deliverable->approval_stage ?? $stages[0];
        if ($stage === 'Assignee') $stage = 'Writer';

        $currentIndex = array_search($stage, $stages);
        if ($currentIndex === false || $currentIndex >= count($stages) - 1) {
            return null;
        }

        $nextStage = $stages[$currentIndex + 1];

        // Route Approver → Further Approver stage when a further approver is selected.
        $routingToFurtherApprover = ($stage === 'Approver' && !empty($data['further_approver_id']));
        if ($routingToFurtherApprover) {
            $nextStage = 'Further Approver';
        } elseif ($nextStage === 'Further Approver') {
            // Skip 'Further Approver' when no further approver is being assigned
            $nextStage = 'Brand Manager';
        }

        return $nextStage;
    }

    /**
     * Determine previous stage in campaign outline workflow.
     */
    public function getPrevStage(Deliverable $deliverable): ?string
    {
        $stages = $this->getStages();
        $stage = $deliverable->approval_stage ?? $stages[0];
        if ($stage === 'Assignee') $stage = 'Writer';

        $currentIndex = array_search($stage, $stages);
        if ($currentIndex !== false && $currentIndex > 0) {
            return $stages[$currentIndex - 1];
        }
        return null;
    }

    /**
     * Get the database column representing the user responsible for a stage.
     */
    public function getRequiredFieldForStage(string $stage): ?string
    {
        return match ($stage) {
            'Writer', 'Writer Review'                  => 'writer_id',
            'Approver', 'Approver Review'              => 'approver_id',
            'Further Approver'                         => 'further_approver_id',
            'Brand Manager', 'AM/BD', 'Final Approval' => 'brand_manager_id',
            'Coordinator'                              => 'coordinator_id',
            'Designer'                                 => 'designer_id',
            default                                    => null,
        };
    }

    /**
     * Get user who should be notified for a specific stage.
     */
    public function getNotifyTarget(Deliverable $deliverable, string $stage): ?User
    {
        $target = match ($stage) {
            'Approver', 'Approver Review'              => $deliverable->approver ?? $deliverable->project?->approver,
            'Further Approver'                         => $deliverable->furtherApprover ?? $deliverable->approver ?? $deliverable->project?->approver,
            'Brand Manager', 'AM/BD', 'Final Approval' => $deliverable->brandManager ?? $deliverable->project?->brandManager,
            'Coordinator'                              => $deliverable->coordinator ?? $deliverable->project?->coordinator,
            'Designer'                                 => $deliverable->designer ?? $deliverable->project?->designer,
            'Writer Review', 'Closed', 'Writer'        => $deliverable->writer ?? $deliverable->project?->writer,
            default                                    => null,
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
     * Validate prerequisites for advancing an Outline deliverable.
     */
    public function validateAdvance(Deliverable $deliverable, array $data, ?User $user): ?array
    {
        $stages = $this->getStages();
        $nextStage = $this->getNextStage($deliverable, $data);

        if (!$nextStage) {
            return ['success' => false, 'message' => 'Deliverable is already at the final stage.', 'code' => 400];
        }

        $oldStage = $deliverable->approval_stage ?? $stages[0];
        if ($oldStage === 'Assignee') $oldStage = 'Writer';

        $hasFurtherApprover = !empty($data['further_approver_id']) && in_array($oldStage, ['Brand Manager', 'AM/BD', 'Final Approval']);

        $requiredField = $this->getRequiredFieldForStage($nextStage);
        if ($requiredField && !$hasFurtherApprover) {
            $assignedId = $data[$requiredField] ?? $deliverable->{$requiredField};
            if (!$assignedId && $deliverable->project) {
                $assignedId = $deliverable->project->{$requiredField};
            }

            if (!$assignedId) {
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
            $stageFieldMap = [
                'Writer'           => 'writer_id',
                'Writer Review'    => 'writer_id',
                'Approver'         => 'approver_id',
                'Approver Review'  => 'approver_id',
                'Further Approver' => 'further_approver_id',
                'Brand Manager'    => 'brand_manager_id',
                'AM/BD'            => 'brand_manager_id',
                'Final Approval'   => 'brand_manager_id',
                'Coordinator'      => 'coordinator_id',
                'Designer'         => 'designer_id',
            ];
            $field = $stageFieldMap[$oldStage] ?? null;
            $assignedId = $field ? $deliverable->{$field} : null;
            if ($assignedId && $user->id != $assignedId) {
                $stageLabel = $oldStage === 'AM/BD' ? 'AM/BD' : strtolower($oldStage);
                return [
                    'success' => false,
                    'message' => "Only the assigned {$stageLabel} can submit this deliverable.",
                    'code'    => 403,
                ];
            }
        }

        // Designer upload gate
        if ($oldStage === 'Designer') {
            $hasUpload = isset($data['final_designs_file']) && $data['final_designs_file'] instanceof \Illuminate\Http\UploadedFile;
            $hasDesigns = $deliverable->final_designs
                || $deliverable->final_designs_link
                || ($data['final_designs'] ?? null)
                || ($data['final_designs_link'] ?? null)
                || $hasUpload;

            if (!$hasDesigns) {
                return [
                    'success' => false,
                    'message' => 'Please upload the final artwork or provide an artwork link before submitting.',
                    'code' => 422
                ];
            }
        }

        return null;
    }

    /**
     * Advance Campaign Outline deliverable to next stage.
     */
    public function advanceStage(Deliverable $deliverable, array $data, ?User $user, bool $dryRun = false): array
    {
        $validationError = $this->validateAdvance($deliverable, $data, $user);
        if ($validationError !== null) {
            return $validationError;
        }

        $stages = $this->getStages();
        $oldStage = $deliverable->approval_stage ?? $stages[0];
        if ($oldStage === 'Assignee') $oldStage = 'Writer';
        $nextStage = $this->getNextStage($deliverable, $data);

        $hoursSpent = isset($data['hours_spent']) && is_numeric($data['hours_spent']) && $data['hours_spent'] > 0
            ? (float) $data['hours_spent'] : null;

        if ($dryRun) {
            return ['success' => true];
        }

        $routingToFurtherApprover = ($oldStage === 'Approver' && !empty($data['further_approver_id']));

        // Brand Manager / AM/BD / Final Approval delegation
        if (in_array($oldStage, ['Brand Manager', 'AM/BD', 'Final Approval']) && !empty($data['further_approver_id'])) {
            $furtherApproverId = (int) $data['further_approver_id'];
            $deliverable->brand_manager_id = $furtherApproverId;
            if ($hoursSpent) {
                $deliverable->work_hours = ($deliverable->work_hours ?? 0) + $hoursSpent;
            }
            $deliverable->save();

            $bmApprovalData = [
                'user_id' => $user?->id ?? auth()->id(),
                'stage' => $oldStage,
                'notes' => ($data['submit_notes'] ?? null)
            ];
            if ($hoursSpent) $bmApprovalData['hours_spent'] = $hoursSpent;
            $deliverable->approvalsHistory()->create($bmApprovalData);

            $furtherApprover = User::find($furtherApproverId);
            if ($furtherApprover) {
                try {
                    $furtherApprover->notify(new DeliverableUpdated(
                        $deliverable,
                        'sent **' . $deliverable->title . '** for your approval',
                        'stage_update',
                        $user ?? auth()->user()
                    ));
                } catch (\Throwable $e) {
                    Log::warning('Failed to send DeliverableUpdated notification: ' . $e->getMessage());
                }
            }

            return ['success' => true, 'message' => 'Deliverable sent to ' . ($furtherApprover->name ?? 'further approver') . ' for additional approval.'];
        }

        // Record who performed current stage (if FK not already set)
        $currentStageField = $this->getRequiredFieldForStage($oldStage);
        if ($currentStageField && !$deliverable->{$currentStageField}) {
            $deliverable->{$currentStageField} = $user?->id ?? auth()->id();
        }

        // Content updates
        if (isset($data['title'])) $deliverable->title = $data['title'];
        if (isset($data['concept'])) $deliverable->concept = $data['concept'];
        if (isset($data['notes'])) $deliverable->notes = $data['notes'];
        if (isset($data['caption'])) $deliverable->caption = $data['caption'];
        if (isset($data['post_copy'])) $deliverable->post_copy = $data['post_copy'];
        if (isset($data['description'])) $deliverable->description = $data['description'];
        if (isset($data['reference'])) $deliverable->reference = $data['reference'];
        if (isset($data['reference_file'])) $deliverable->reference_file = $data['reference_file'];

        // Stakeholder updates
        if (isset($data['writer_id'])) $deliverable->writer_id = $data['writer_id'];
        if (isset($data['approver_id'])) $deliverable->approver_id = $data['approver_id'];
        if ($routingToFurtherApprover) {
            $deliverable->further_approver_id = (int) $data['further_approver_id'];
        }
        if (isset($data['brand_manager_id'])) $deliverable->brand_manager_id = $data['brand_manager_id'];
        if (isset($data['coordinator_id'])) $deliverable->coordinator_id = $data['coordinator_id'];
        if (isset($data['designer_id'])) $deliverable->designer_id = $data['designer_id'];
        if (array_key_exists('designer_deadline', $data)) {
            $deliverable->designer_deadline = $data['designer_deadline'] ?: null;
        }

        // Designer Delivery
        if ($oldStage === 'Designer') {
            if (isset($data['final_designs'])) $deliverable->final_designs = $data['final_designs'];
            if (isset($data['final_designs_link'])) $deliverable->final_designs_link = $data['final_designs_link'];
            
            if (isset($data['final_designs_file'])) {
                if (is_string($data['final_designs_file'])) {
                    $deliverable->final_designs = \Illuminate\Support\Facades\Storage::disk('s3')->url(ltrim($data['final_designs_file'], '/'));
                } elseif ($data['final_designs_file'] instanceof \Illuminate\Http\UploadedFile) {
                    $deliverable->final_designs = $this->moveUploadedFile($data['final_designs_file'], 'artwork');
                }
            }
        }

        // Reset client status when advancing
        $deliverable->client_status = null;

        $deliverable->approval_stage = $nextStage;
        $deliverable->progress_percent = $this->getStageProgress($deliverable);
        $deliverable->revision_instructions = null;
        $deliverable->status = ($nextStage === 'Closed' || $nextStage === 'closed') ? 'Done' : 'To Do';
        $deliverable->is_ready = false;
        if ($hoursSpent) {
            $deliverable->work_hours = ($deliverable->work_hours ?? 0) + $hoursSpent;
        }
        $deliverable->save();

        // History
        $approvalData = [
            'user_id' => $user?->id ?? auth()->id(),
            'stage' => $oldStage,
            'notes' => $data['submit_notes'] ?? null
        ];
        if ($hoursSpent) $approvalData['hours_spent'] = $hoursSpent;
        $deliverable->approvalsHistory()->create($approvalData);
        $deliverable->revisionsHistory()->whereNull('fixed_by_user_id')->latest()->first()?->update([
            'fixed_by_user_id' => $user?->id ?? auth()->id(),
            'fixed_at' => now()
        ]);

        // Notify
        $deliverable->notifyStageChange($oldStage, $nextStage, $user ?? auth()->user());

        return ['success' => true, 'message' => "Deliverable submitted to {$nextStage} stage."];
    }

    /**
     * Handle revision request in Campaign Outline workflow.
     */
    public function requestRevisions(Deliverable $deliverable, array $validatedData, ?string $imagePath, ?User $user): array
    {
        $stages = $this->getStages();
        $firstStage = $stages[0]; // 'Writer'

        if ($deliverable->approval_stage === $firstStage) {
            return ['success' => false, 'message' => 'Cannot request revisions for this stage.', 'code' => 422];
        }

        $oldStage = $deliverable->approval_stage;

        if (in_array($oldStage, ['Final Approval', 'AM/BD', 'Approver Review', 'Writer Review'])) {
            $target = $validatedData['revision_target'] ?? 'designer';
            if ($target === 'writer') {
                $deliverable->approval_stage = $firstStage;
            } else {
                $deliverable->approval_stage = 'Designer';
            }
        } else {
            $deliverable->approval_stage = $firstStage;
        }

        $deliverable->status = 'To Do';
        $deliverable->progress_percent = $this->getStageProgress($deliverable);
        $deliverable->revisions += 1;
        $deliverable->revision_instructions = $validatedData['revision_instructions'];
        $deliverable->save();

        // History
        $deliverable->revisionsHistory()->create([
            'user_id' => $user?->id ?? auth()->id(),
            'instructions' => $validatedData['revision_instructions'],
            'image_path' => $imagePath,
            'stage_at_revision' => $oldStage,
        ]);

        // Notify
        $notifyTarget = ($deliverable->approval_stage === 'Designer')
            ? ($deliverable->designer ?? $deliverable->project?->designer)
            : ($deliverable->writer ?? $deliverable->project?->writer);

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

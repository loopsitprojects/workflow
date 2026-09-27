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
 * Encapsulates the 4-stage workflow pipeline for Campaign and Pitch projects.
 * Isolated from RetainerWorkflowService so changes here never affect retainer flows.
 */
class CampaignWorkflowService implements WorkflowInterface
{
    use HandlesWorkflowUploads;

    public const STAGES = [
        'Assignee',
        'AM/BD',
        'Final Approval',
        'Closed'
    ];

    /**
     * Get the ordered stages for campaign/pitch workflow.
     */
    public function getStages(): array
    {
        return self::STAGES;
    }

    /**
     * Calculate stage progress percentage for campaign/pitch workflow.
     */
    public function getStageProgress(Deliverable $deliverable): int
    {
        $stages = $this->getStages();
        $index = array_search($deliverable->approval_stage ?? $stages[0], $stages);
        if ($index === false) return 0;
        
        $milestones = [10, 50, 90, 100];
        return $milestones[$index] ?? 0;
    }

    /**
     * Determine next stage in campaign workflow.
     */
    public function getNextStage(Deliverable $deliverable, array $data = []): ?string
    {
        $stages = $this->getStages();
        $currentIndex = array_search($deliverable->approval_stage ?? $stages[0], $stages);
        if ($currentIndex !== false && $currentIndex < count($stages) - 1) {
            return $stages[$currentIndex + 1];
        }
        return null;
    }

    /**
     * Determine previous stage in campaign workflow.
     */
    public function getPrevStage(Deliverable $deliverable): ?string
    {
        $stages = $this->getStages();
        $currentIndex = array_search($deliverable->approval_stage ?? $stages[0], $stages);
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
            'Assignee'                  => 'writer_id',
            'AM/BD', 'Final Approval'   => 'brand_manager_id',
            default                     => null,
        };
    }

    /**
     * Get user who should be notified for a specific stage.
     */
    public function getNotifyTarget(Deliverable $deliverable, string $stage): ?User
    {
        $target = match ($stage) {
            'AM/BD', 'Final Approval' => $deliverable->brandManager ?? $deliverable->project?->brandManager,
            'Assignee', 'Closed'      => $deliverable->writer ?? $deliverable->project?->writer,
            default                   => null,
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
     * Validate prerequisites for advancing a Campaign deliverable.
     */
    public function validateAdvance(Deliverable $deliverable, array $data, ?User $user): ?array
    {
        $stages = $this->getStages();
        $nextStage = $this->getNextStage($deliverable, $data);

        if (!$nextStage) {
            return ['success' => false, 'message' => 'Deliverable is already at the final stage.', 'code' => 400];
        }

        $oldStage = $deliverable->approval_stage ?? $stages[0];

        $requiredField = $this->getRequiredFieldForStage($nextStage);
        if ($requiredField) {
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
                'Assignee'       => 'writer_id',
                'AM/BD'          => 'brand_manager_id',
                'Final Approval' => 'brand_manager_id',
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

        return null;
    }

    /**
     * Advance Campaign deliverable to next stage.
     */
    public function advanceStage(Deliverable $deliverable, array $data, ?User $user, bool $dryRun = false): array
    {
        $validationError = $this->validateAdvance($deliverable, $data, $user);
        if ($validationError !== null) {
            return $validationError;
        }

        $stages = $this->getStages();
        $oldStage = $deliverable->approval_stage ?? $stages[0];
        $nextStage = $this->getNextStage($deliverable, $data);

        $hoursSpent = isset($data['hours_spent']) && is_numeric($data['hours_spent']) && $data['hours_spent'] > 0
            ? (float) $data['hours_spent'] : null;

        if ($dryRun) {
            return ['success' => true];
        }

        // Record who performed current stage (if FK not already set)
        $currentStageField = $this->getRequiredFieldForStage($oldStage);
        if ($currentStageField && !$deliverable->{$currentStageField}) {
            $deliverable->{$currentStageField} = $user?->id ?? auth()->id();
        }

        // Content updates
        if (isset($data['title'])) $deliverable->title = $data['title'];
        if (isset($data['notes'])) $deliverable->notes = $data['notes'];
        if (isset($data['description'])) $deliverable->description = $data['description'];
        if (isset($data['reference'])) $deliverable->reference = $data['reference'];
        if (isset($data['reference_file'])) $deliverable->reference_file = $data['reference_file'];

        // Stakeholder updates
        if (isset($data['writer_id'])) $deliverable->writer_id = $data['writer_id'];
        if (isset($data['brand_manager_id'])) $deliverable->brand_manager_id = $data['brand_manager_id'];

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
     * Handle revision request in Campaign workflow.
     */
    public function requestRevisions(Deliverable $deliverable, array $validatedData, ?string $imagePath, ?User $user): array
    {
        $stages = $this->getStages();
        $firstStage = $stages[0]; // 'Assignee'

        if ($deliverable->approval_stage === $firstStage) {
            return ['success' => false, 'message' => 'Cannot request revisions for this stage.', 'code' => 422];
        }

        $oldStage = $deliverable->approval_stage;

        // Reset back to first stage (Assignee)
        $deliverable->approval_stage = $firstStage;
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

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
 * Implements the Direct Design workflow:
 * 1. Designer (In Design / Artwork Upload -> Assigns Writer)
 * 2. Writer Review (Writer Approval -> Assigns Approver)
 * 3. Approver Review (Approver Review -> Optionally assigns Further Approver or sends to Brand Manager)
 * 4. Further Approver (Further Review -> Sends to Brand Manager)
 * 5. Final Approval (Brand Manager -> Closes deliverable)
 * 6. Closed (Finalized)
 *
 * Completely isolated from RetainerWorkflowService, CampaignWorkflowService,
 * and OtherDeliverableWorkflowService.
 */
class DirectDesignWorkflowService implements WorkflowInterface
{
    use HandlesWorkflowUploads;

    public const STAGES = [
        'Designer',
        'Writer Review',
        'Approver Review',
        'Further Approver',
        'Final Approval',
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
        if (in_array($lower, ['writer review', 'writer', 'writer approval'])) return 'Writer Review';
        if (in_array($lower, ['approver review', 'approver', 'approver approval'])) return 'Approver Review';
        if (in_array($lower, ['further approver', 'further approval', 'further approver review'])) return 'Further Approver';
        if (in_array($lower, ['final approval', 'brand manager', 'manager review', 'bm review', 'manager approval', 'am/bd'])) return 'Final Approval';
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
     * Designer: 20%, Writer Review: 40%, Approver Review: 60%, Further Approver: 75%, Final Approval: 85%, Closed: 100%
     */
    public function getStageProgress(Deliverable $deliverable): int
    {
        $stage = $this->normalizeStage($deliverable->approval_stage);
        return match ($stage) {
            'Designer'         => 20,
            'Writer Review'    => 40,
            'Approver Review'  => 60,
            'Further Approver' => 75,
            'Final Approval'   => 85,
            'Closed'           => 100,
            default            => 20,
        };
    }

    /**
     * Determine next stage in workflow.
     * When at Designer stage, Writer Review is optional:
     * - Can be sent straight to Approver Review (mandatory approver)
     * - Or sent to Writer Review (writer reviews, then moves to mandatory approver)
     */
    public function getNextStage(Deliverable $deliverable, array $data = []): ?string
    {
        $currentStage = $this->normalizeStage($deliverable->approval_stage);

        if ($currentStage === 'Designer') {
            $target = strtolower(trim($data['next_stage'] ?? $data['target_stage'] ?? $data['route_to'] ?? $data['send_to'] ?? ''));
            $skipWriter = !empty($data['skip_writer']) && filter_var($data['skip_writer'], FILTER_VALIDATE_BOOLEAN);

            // Explicit target to approver / skip writer
            if (in_array($target, ['approver review', 'approver', 'approver_review']) || $skipWriter) {
                return 'Approver Review';
            }

            // Explicit target to writer review
            if (in_array($target, ['writer review', 'writer', 'writer_review'])) {
                return 'Writer Review';
            }

            // If approver_id is provided and writer_id is not
            if (!empty($data['approver_id']) && empty($data['writer_id'])) {
                return 'Approver Review';
            }

            // If writer_id is provided
            if (!empty($data['writer_id'])) {
                return 'Writer Review';
            }

            // If deliverable already has approver assigned and no writer assigned or available on project
            if ($deliverable->approver_id && !$deliverable->writer_id && !$deliverable->project?->writer_id) {
                return 'Approver Review';
            }

            // Default fallback: Writer Review
            return 'Writer Review';
        }

        return match ($currentStage) {
            'Writer Review'    => 'Approver Review',
            'Approver Review'  => !empty($data['further_approver_id']) ? 'Further Approver' : 'Final Approval',
            'Further Approver' => 'Final Approval',
            'Final Approval'   => 'Closed',
            default            => null,
        };
    }

    /**
     * Determine previous stage in workflow.
     */
    public function getPrevStage(Deliverable $deliverable): ?string
    {
        $currentStage = $this->normalizeStage($deliverable->approval_stage);
        if ($currentStage === 'Approver Review') {
            $hadWriterReview = $deliverable->approvalsHistory()->where('stage', 'Writer Review')->exists();
            return $hadWriterReview ? 'Writer Review' : 'Designer';
        }

        return match ($currentStage) {
            'Closed'           => 'Final Approval',
            'Final Approval'   => $deliverable->further_approver_id ? 'Further Approver' : 'Approver Review',
            'Further Approver' => 'Approver Review',
            'Writer Review'    => 'Designer',
            default            => null,
        };
    }

    /**
     * Get the database column representing the user responsible for a stage.
     */
    public function getRequiredFieldForStage(string $stage): ?string
    {
        $norm = $this->normalizeStage($stage);
        return match ($norm) {
            'Designer'         => 'designer_id',
            'Writer Review'    => 'writer_id',
            'Approver Review'  => 'approver_id',
            'Further Approver' => 'further_approver_id',
            'Final Approval'   => 'brand_manager_id',
            default            => null,
        };
    }

    /**
     * Get user who should be notified for a specific stage.
     */
    public function getNotifyTarget(Deliverable $deliverable, string $stage): ?User
    {
        $norm = $this->normalizeStage($stage);
        $target = match ($norm) {
            'Designer'         => $deliverable->designer ?? ($deliverable->designer_id ? User::find($deliverable->designer_id) : null),
            'Writer Review'    => $deliverable->writer ?? ($deliverable->writer_id ? User::find($deliverable->writer_id) : null),
            'Approver Review'  => $deliverable->approver ?? ($deliverable->approver_id ? User::find($deliverable->approver_id) : null),
            'Further Approver' => $deliverable->furtherApprover ?? ($deliverable->further_approver_id ? User::find($deliverable->further_approver_id) : null),
            'Final Approval'   => $deliverable->brandManager ?? $deliverable->project?->brandManager ?? $deliverable->project?->lead,
            'Closed'           => $deliverable->designer ?? $deliverable->brandManager,
            default            => null,
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
        $userRole = strtolower(str_replace(' ', '', $user->role ?? ''));
        $isPrivileged = $user && ($user->isAdmin() || in_array($userRole, ['operationsmanager', 'brandmanager']));

        if ($oldStage === 'Designer') {
            $isAssignedDesigner = $deliverable->designer_id && $user && $user->id == $deliverable->designer_id;
            $isUnassignedDesigner = !$deliverable->designer_id && $userRole === 'designer';

            if (!$isAssignedDesigner && !$isUnassignedDesigner && !$isPrivileged) {
                return [
                    'success' => false,
                    'message' => 'Direct design deliverables in Designer stage can only be submitted by the assigned designer.',
                    'code'    => 403,
                ];
            }

            if ($nextStage === 'Approver Review') {
                // Mandatory Approver check: Approver stage is mandatory
                if (empty($data['approver_id']) && !$deliverable->approver_id) {
                    if ($deliverable->project?->approver_id) {
                        $deliverable->approver_id = $deliverable->project->approver_id;
                    } else {
                        return [
                            'success' => false,
                            'message' => 'Please select an approver for approver review.',
                            'code'    => 422,
                        ];
                    }
                }
            } elseif ($nextStage === 'Writer Review') {
                // Require writer selection to advance to Writer Review
                if (empty($data['writer_id']) && !$deliverable->writer_id) {
                    if ($deliverable->project?->writer_id) {
                        $deliverable->writer_id = $deliverable->project->writer_id;
                    } else {
                        $fallbackWriter = $deliverable->project?->brand?->members()->whereIn('role', ['Writer', 'Assignee'])->first()
                            ?? User::whereIn('role', ['Writer', 'Assignee'])->first();
                        if ($fallbackWriter) {
                            $deliverable->writer_id = $fallbackWriter->id;
                        } else {
                            return [
                                'success' => false,
                                'message' => 'Please select a writer for writer approval.',
                                'code'    => 422,
                            ];
                        }
                    }
                }
            }
        } elseif ($oldStage === 'Writer Review') {
            $isAssignedWriter = $deliverable->writer_id && $user && $user->id == $deliverable->writer_id;
            $isUnassignedWriter = !$deliverable->writer_id && in_array($userRole, ['writer', 'assignee']);

            if (!$isAssignedWriter && !$isUnassignedWriter && !$isPrivileged) {
                return [
                    'success' => false,
                    'message' => 'Only the assigned writer can approve and advance this deliverable.',
                    'code'    => 403,
                ];
            }

            // Require approver selection to advance to Approver Review
            if (empty($data['approver_id']) && !$deliverable->approver_id) {
                return [
                    'success' => false,
                    'message' => 'Please select an approver for approver review.',
                    'code'    => 422,
                ];
            }
        } elseif ($oldStage === 'Approver Review') {
            $isAssignedApprover = $deliverable->approver_id && $user && $user->id == $deliverable->approver_id;
            $isApproverRole = in_array($userRole, ['approver', 'approvercoordinator']);

            if (!$isAssignedApprover && !$isApproverRole && !$isPrivileged) {
                return [
                    'success' => false,
                    'message' => 'Only the assigned approver can approve and advance this deliverable.',
                    'code'    => 403,
                ];
            }
        } elseif ($oldStage === 'Further Approver') {
            $isAssignedFurtherApprover = $deliverable->further_approver_id && $user && $user->id == $deliverable->further_approver_id;
            $isApproverRole = in_array($userRole, ['approver', 'approvercoordinator']);

            if (!$isAssignedFurtherApprover && !$isApproverRole && !$isPrivileged) {
                return [
                    'success' => false,
                    'message' => 'Only the assigned further approver can approve and advance this deliverable.',
                    'code'    => 403,
                ];
            }
        } elseif ($oldStage === 'Final Approval') {
            $isBrandManager = $deliverable->brand_manager_id && $user && $user->id == $deliverable->brand_manager_id;

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
     * Advance Direct Design Deliverable to next stage.
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

        // Assign writer if passed
        if (!empty($data['writer_id'])) {
            $deliverable->writer_id = $data['writer_id'];
        } elseif ($nextStage === 'Writer Review' && !$deliverable->writer_id && $deliverable->project?->writer_id) {
            $deliverable->writer_id = $deliverable->project->writer_id;
        }

        // Assign approver if passed
        if (!empty($data['approver_id'])) {
            $deliverable->approver_id = $data['approver_id'];
        } elseif ($nextStage === 'Approver Review' && !$deliverable->approver_id && $deliverable->project?->approver_id) {
            $deliverable->approver_id = $deliverable->project->approver_id;
        }

        // Assign further approver if passed (or clear if empty/skipped when transitioning from Approver Review directly to Final Approval)
        if ($oldStage === 'Approver Review' && $nextStage === 'Final Approval') {
            $deliverable->further_approver_id = null;
        } elseif (array_key_exists('further_approver_id', $data)) {
            $deliverable->further_approver_id = !empty($data['further_approver_id']) ? $data['further_approver_id'] : null;
        }

        // Assign or auto-assign brand manager
        if (!empty($data['brand_manager_id'])) {
            $deliverable->brand_manager_id = $data['brand_manager_id'];
        } elseif (!$deliverable->brand_manager_id && $deliverable->project?->brand_manager_id) {
            $deliverable->brand_manager_id = $deliverable->project->brand_manager_id;
        }

        // Process file uploads if instances of UploadedFile
        if (isset($data['reference_file']) && $data['reference_file'] instanceof \Illuminate\Http\UploadedFile) {
            $deliverable->reference_file = $this->moveUploadedFile($data['reference_file'], 'references');
            $deliverable->reference_uploaded_by = $user->id;
        } elseif (isset($data['reference_file'])) {
            $deliverable->reference_file = $data['reference_file'];
            $deliverable->reference_uploaded_by = $user->id;
        }

        if (isset($data['final_designs']) && $data['final_designs'] instanceof \Illuminate\Http\UploadedFile) {
            $deliverable->final_designs = $this->moveUploadedFile($data['final_designs'], 'artwork');
            $deliverable->artwork_uploaded_by = $user->id;
        } elseif (isset($data['final_designs'])) {
            $deliverable->final_designs = $data['final_designs'];
            $deliverable->artwork_uploaded_by = $user->id;
        }

        // Content updates
        if (isset($data['title'])) $deliverable->title = $data['title'];
        if (isset($data['concept'])) $deliverable->concept = $data['concept'];
        if (isset($data['notes'])) $deliverable->notes = $data['notes'];
        if (isset($data['caption'])) $deliverable->caption = $data['caption'];
        if (isset($data['post_copy'])) $deliverable->post_copy = $data['post_copy'];
        if (isset($data['reference'])) {
            $deliverable->reference = $data['reference'];
            $deliverable->reference_uploaded_by = $user->id;
        }
        if (isset($data['final_designs_link'])) {
            $deliverable->final_designs_link = $data['final_designs_link'];
            $deliverable->artwork_uploaded_by = $user->id;
        }
        if (isset($data['designer_deadline'])) {
            $deliverable->designer_deadline = $data['designer_deadline'];
            $deliverable->deadline = $data['designer_deadline'];
        }
        if (isset($data['deadline'])) {
            $deliverable->deadline = $data['deadline'];
            $deliverable->designer_deadline = $data['deadline'];
        }

        // Reset client status when advancing
        $deliverable->client_status = null;

        // Stage progression
        $deliverable->approval_stage = $nextStage;
        $deliverable->progress_percent = $this->getStageProgress($deliverable);
        $deliverable->revision_instructions = null;
        $deliverable->revision_deadline = null;
        $deliverable->revision_target = null;
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
            'Writer Review'    => 'Artwork submitted and sent to Writer for review.',
            'Approver Review'  => ($oldStage === 'Designer')
                ? 'Artwork submitted and sent directly to Approver for review.'
                : 'Deliverable approved by Writer and sent to Approver.',
            'Further Approver' => 'Deliverable sent to Further Approver.',
            'Final Approval'   => 'Deliverable sent to Brand Manager for final approval.',
            'Closed'           => 'Deliverable approved and closed.',
            default            => "Deliverable advanced to {$nextStage} stage.",
        };
        return ['success' => true, 'message' => $msg];
    }

    /**
     * Handle revision request in Direct Design workflow.
     * Routes back to Designer from any review stage.
     */
    public function requestRevisions(Deliverable $deliverable, array $validatedData, ?string $imagePath, ?User $user): array
    {
        $oldStage = $this->normalizeStage($deliverable->approval_stage);

        if ($oldStage === 'Designer') {
            return ['success' => false, 'message' => 'Cannot request revisions for a deliverable already in Designer stage.', 'code' => 422];
        }

        // Reset back to Designer
        $deliverable->approval_stage = 'Designer';
        $deliverable->status = 'In Progress';
        $deliverable->progress_percent = $this->getStageProgress($deliverable);
        $deliverable->revisions += 1;
        $deliverable->revision_instructions = $validatedData['revision_instructions'];
        $deliverable->revision_deadline = $validatedData['revision_deadline'] ?? null;
        $deliverable->revision_target = 'designer';
        $deliverable->save();

        // History
        $deliverable->revisionsHistory()->create([
            'user_id'           => $user?->id ?? auth()->id(),
            'instructions'      => $validatedData['revision_instructions'],
            'image_path'        => $imagePath,
            'stage_at_revision' => $oldStage,
            'revision_deadline' => $validatedData['revision_deadline'] ?? null,
            'target'            => 'designer',
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

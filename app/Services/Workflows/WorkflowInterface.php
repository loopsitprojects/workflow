<?php

namespace App\Services\Workflows;

use App\Models\Deliverable;
use App\Models\User;

interface WorkflowInterface
{
    /**
     * Get the ordered list of stage names for this workflow.
     */
    public function getStages(): array;

    /**
     * Calculate stage progress percentage.
     */
    public function getStageProgress(Deliverable $deliverable): int;

    /**
     * Determine the next stage.
     */
    public function getNextStage(Deliverable $deliverable, array $data = []): ?string;

    /**
     * Determine the previous stage.
     */
    public function getPrevStage(Deliverable $deliverable): ?string;

    /**
     * Get the database column representing the user responsible for a stage.
     */
    public function getRequiredFieldForStage(string $stage): ?string;

    /**
     * Get the user who should be notified when entering this stage.
     */
    public function getNotifyTarget(Deliverable $deliverable, string $stage): ?User;

    /**
     * Validate prerequisites before advancing (permissions, mandatory fields, artwork uploads, etc.).
     * Returns null if valid, or an error array: ['success' => false, 'message' => string, 'code' => int].
     */
    public function validateAdvance(Deliverable $deliverable, array $data, ?User $user): ?array;

    /**
     * Advance the deliverable stage and update associated metadata.
     * Returns ['success' => bool, 'message' => string, 'code' => int].
     */
    public function advanceStage(Deliverable $deliverable, array $data, ?User $user, bool $dryRun = false): array;

    /**
     * Handle revision request for this deliverable.
     * Returns ['success' => bool, 'message' => string, 'code' => int].
     */
    public function requestRevisions(Deliverable $deliverable, array $validatedData, ?string $imagePath, ?User $user): array;
}

<?php

namespace App\Services\Workflows;

use App\Models\Deliverable;

/**
 * WorkflowManager
 *
 * Resolves the appropriate workflow service driver for a deliverable or project.
 * Ensures the Retainer workflow remains completely decoupled from other workflows.
 */
class WorkflowManager
{
    /**
     * Resolve the workflow service for a given deliverable.
     */
    public static function for(Deliverable $deliverable): WorkflowInterface
    {
        $type = $deliverable->project?->workflow_type ?? 'retainer';
        return self::forType($type);
    }

    /**
     * Resolve the workflow service for a given workflow type string.
     */
    public static function forType(?string $type): WorkflowInterface
    {
        return match ($type) {
            'campaign', 'pitch' => app(CampaignWorkflowService::class),
            default             => app(RetainerWorkflowService::class),
        };
    }
}

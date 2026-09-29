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
        if ($deliverable->isDirectDesign()) {
            return app(DirectDesignWorkflowService::class);
        }

        $type = $deliverable->project?->workflow_type ?? 'retainer';
        if ($type === 'retainer') {
            return app(RetainerWorkflowService::class);
        }

        // For campaign and pitch projects:
        // Outlines use CampaignWorkflowService
        // Other deliverable types use OtherDeliverableWorkflowService (Assign -> Approve -> Close)
        $postType = $deliverable->post_type ?? $deliverable->parent?->post_type;
        $normType = strtolower(trim($postType ?? ''));
        if ($normType === 'outlines' || $normType === 'outline') {
            return app(CampaignWorkflowService::class);
        }

        return app(OtherDeliverableWorkflowService::class);
    }

    /**
     * Resolve the workflow service for a given workflow type string, optional post type, and optional flow type.
     */
    public static function forType(?string $type, ?string $postType = null, ?string $flowType = null): WorkflowInterface
    {
        if ($flowType === 'direct_design') {
            return app(DirectDesignWorkflowService::class);
        }
        if ($type === 'campaign' || $type === 'pitch') {
            $normType = strtolower(trim($postType ?? ''));
            if ($normType === 'outlines' || $normType === 'outline') {
                return app(CampaignWorkflowService::class);
            }
            if (!empty($normType)) {
                return app(OtherDeliverableWorkflowService::class);
            }
            return app(CampaignWorkflowService::class);
        }

        return app(RetainerWorkflowService::class);
    }
}

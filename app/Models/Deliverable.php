<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deliverable extends Model
{
    protected $attributes = [
        'priority' => 'Medium',
        'status' => 'To Do',
    ];

    protected $fillable = [
        'project_id',
        'parent_deliverable_id',
        'title',
        'description',
        'status',
        'priority',
        'assignee_name',
        'deadline',
        'image_url',
        'task_type',
        'progress_percent',
        'is_ready',
        // Retainer / content fields
        'post_type',
        'concept',
        'caption',
        'post_copy',
        'reference',
        // Schedule fields
        'start_date',
        'end_date',
        'approver_id',
        'further_approver_id',
        'writer_id',
        'brand_manager_id',
        'coordinator_id',
        'designer_id',
        'approval_stage',
        'final_designs',
        'final_designs_link',
        'revisions',
        'revision_instructions',
        'reference_file',
        'notes',
        'work_hours',
        'client_status',
        'designer_deadline',
    ];

    protected $casts = [
        'designer_deadline' => 'datetime',
    ];

    protected $appends = [
        'reference_files_list',
        'reference_urls_list',
        'final_designs_list',
        'final_designs_urls_list',
        'workflow_stages',
    ];

    public function getReferenceFilesArray(): array
    {
        if (empty($this->reference_file)) {
            return [];
        }
        $val = trim(trim($this->reference_file), '"\'');
        if (str_starts_with($val, '[') && str_ends_with($val, ']')) {
            $decoded = json_decode($val, true);
            if (is_array($decoded)) {
                return array_values(array_filter(array_map(fn($item) => is_string($item) ? trim($item, '"\'') : $item, $decoded)));
            }
        }
        return [$val];
    }

    public function getReferenceUrlsArray(): array
    {
        if (empty($this->reference)) {
            return [];
        }
        $val = trim(trim($this->reference), '"\'');
        if (str_starts_with($val, '[') && str_ends_with($val, ']')) {
            $decoded = json_decode($val, true);
            if (is_array($decoded)) {
                return array_values(array_filter(array_map(fn($item) => is_string($item) ? trim($item, '"\'') : $item, $decoded)));
            }
        }
        $urls = preg_split('/[\r\n,]+/', $val);
        return array_values(array_filter(array_map(fn($u) => trim($u, '"\' '), $urls)));
    }

    public function getFinalDesignsArray(): array
    {
        if (empty($this->final_designs)) {
            return [];
        }
        $val = trim(trim($this->final_designs), '"\'');
        if (str_starts_with($val, '[') && str_ends_with($val, ']')) {
            $decoded = json_decode($val, true);
            if (is_array($decoded)) {
                return array_values(array_filter(array_map(fn($item) => is_string($item) ? trim($item, '"\'') : $item, $decoded)));
            }
        }
        return [$val];
    }

    public function getAllArtworkFiles(): array
    {
        $files = $this->getFinalDesignsArray();

        // If deliverable has subtasks (e.g. Carousel slides), collect each subtask's artwork
        if ($this->subtasks && $this->subtasks->isNotEmpty()) {
            foreach ($this->subtasks as $sub) {
                $subFiles = $sub->getFinalDesignsArray();
                if (empty($subFiles)) {
                    $subFiles = $sub->getReferenceFilesArray();
                }
                if (empty($subFiles) && !empty($sub->image_url)) {
                    $subFiles = [$sub->image_url];
                }
                foreach ($subFiles as $sf) {
                    if (!in_array($sf, $files)) {
                        $files[] = $sf;
                    }
                }
            }
        }

        // Fallback to reference files if no final designs are uploaded
        if (empty($files)) {
            $files = $this->getReferenceFilesArray();
        }

        if (empty($files) && !empty($this->image_url)) {
            $files[] = $this->image_url;
        }

        return array_values(array_unique(array_filter(array_map(fn($u) => trim($u, "\"' \t\n\r\0\x0B"), $files))));
    }

    public function getFinalDesignsUrlsArray(): array
    {
        if (empty($this->final_designs_link)) {
            return [];
        }
        $val = trim(trim($this->final_designs_link), '"\'');
        if (str_starts_with($val, '[') && str_ends_with($val, ']')) {
            $decoded = json_decode($val, true);
            if (is_array($decoded)) {
                return array_values(array_filter(array_map(fn($item) => is_string($item) ? trim($item, '"\'') : $item, $decoded)));
            }
        }
        $urls = preg_split('/[\r\n,]+/', $val);
        return array_values(array_filter(array_map(fn($u) => trim($u, '"\' '), $urls)));
    }

    public function getReferenceFilesListAttribute(): array
    {
        return $this->getReferenceFilesArray();
    }

    public function getReferenceUrlsListAttribute(): array
    {
        return $this->getReferenceUrlsArray();
    }

    public function getFinalDesignsListAttribute(): array
    {
        return $this->getFinalDesignsArray();
    }

    public function getFinalDesignsUrlsListAttribute(): array
    {
        return $this->getFinalDesignsUrlsArray();
    }

    protected static function booted()
    {
        static::saved(function ($deliverable) {
            if ($deliverable->project_id) {
                event(new \App\Events\DeliverablesUpdated($deliverable->project_id));
            }
        });

        static::deleted(function ($deliverable) {
            if ($deliverable->project_id) {
                event(new \App\Events\DeliverablesUpdated($deliverable->project_id));
            }
        });
    }

    const STAGES = [
        'Writer',
        'Approver',
        'Further Approver',
        'Brand Manager',
        'Coordinator',
        'Designer',
        'Writer Review',
        'Approver Review',
        'Final Approval',
        'Scheduled',
        'Closed'
    ];

    const CAMPAIGN_STAGES = [
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

    const OTHER_DELIVERABLE_STAGES = [
        'Assign',
        'Approve',
        'Close'
    ];

    public function isOtherDeliverable(): bool
    {
        $projectType = $this->project?->workflow_type ?? 'retainer';
        if ($projectType === 'retainer') return false;
        $postType = $this->post_type ?? $this->parent?->post_type;
        $normType = strtolower(trim($postType ?? ''));
        return !in_array($normType, ['outlines', 'outline']);
    }

    public function getWorkflow(): \App\Services\Workflows\WorkflowInterface
    {
        return \App\Services\Workflows\WorkflowManager::for($this);
    }

    public function getStages()
    {
        return $this->getWorkflow()->getStages();
    }

    public function getWorkflowStagesAttribute(): array
    {
        return $this->getStages();
    }

    public function getStageProgress()
    {
        return $this->getWorkflow()->getStageProgress($this);
    }

    public function getNextStage()
    {
        return $this->getWorkflow()->getNextStage($this);
    }

    public function getPrevStage()
    {
        return $this->getWorkflow()->getPrevStage($this);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function writer()
    {
        return $this->belongsTo(User::class, 'writer_id');
    }

    public function brandManager()
    {
        return $this->belongsTo(User::class, 'brand_manager_id');
    }

    public function furtherApprover()
    {
        return $this->belongsTo(User::class, 'further_approver_id');
    }

    public function coordinator()
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    public function designer()
    {
        return $this->belongsTo(User::class, 'designer_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function subtasks()
    {
        return $this->hasMany(Deliverable::class, 'parent_deliverable_id');
    }

    public function parent()
    {
        return $this->belongsTo(Deliverable::class, 'parent_deliverable_id');
    }

    public function revisionsHistory()
    {
        return $this->hasMany(DeliverableRevision::class)->latest();
    }

    public function approvalsHistory()
    {
        return $this->hasMany(DeliverableApproval::class)->latest();
    }

    public function reassignments()
    {
        return $this->hasMany(DeliverableReassignment::class)->latest();
    }

    public function getReassignmentsHistoryAttribute()
    {
        return $this->reassignments()->with('fromUser', 'toUser', 'reassignedBy')->get();
    }

    public function getSubtaskTypeAttribute()
    {
        return $this->post_type;
    }

    public function getSubtaskCopyAttribute()
    {
        return $this->post_copy;
    }

    public function getSubtaskTypeColorsAttribute()
    {
        $colors = [
            'Standard' => ['bg' => '#f1f5f9', 'text' => '#475569', 'border' => '#e2e8f0'],
            'Static Post' => ['bg' => '#f0fdf4', 'text' => '#15803d', 'border' => '#bbf7d0'],
            'Carousel'    => ['bg' => '#faf5ff', 'text' => '#7c3aed', 'border' => '#e9d5ff'],
            'Reels'       => ['bg' => '#fef2f2', 'text' => '#ef4444', 'border' => '#fecaca'],
            'Story'       => ['bg' => '#fff7ed', 'text' => '#c2410c', 'border' => '#fed7aa'],
            'Radio script' => ['bg' => '#ecfeff', 'text' => '#0891b2', 'border' => '#a5f3fc'],
            'KV'           => ['bg' => '#fff1f2', 'text' => '#e11d48', 'border' => '#fecdd3'],
            'Presentation' => ['bg' => '#f0f9ff', 'text' => '#0284c7', 'border' => '#bae6fd'],
            'Video script' => ['bg' => '#fdf2f8', 'text' => '#db2777', 'border' => '#fbcfe8'],
            'Ideation/Brainstorm' => ['bg' => '#fefce8', 'text' => '#ca8a04', 'border' => '#fef9c3'],
            'Review'       => ['bg' => '#f0fdfa', 'text' => '#0d9488', 'border' => '#ccfbf1'],
            'Client meeting' => ['bg' => '#f5f3ff', 'text' => '#7c3aed', 'border' => '#ddd6fe'],
            'Internal meeting' => ['bg' => '#f8fafc', 'text' => '#64748b', 'border' => '#e2e8f0'],
            'Upload file'  => ['bg' => '#ecfdf5', 'text' => '#059669', 'border' => '#a7f3d0'],
            'Text field'   => ['bg' => '#fffbeb', 'text' => '#d97706', 'border' => '#fef3c7'],
            'default'     => ['bg' => '#f8fafc', 'text' => '#475569', 'border' => '#e2e8f0'],
        ];

        $type = $this->post_type ?? 'default';
        return $colors[$type] ?? $colors['default'];
    }

    public function getRevisionsHistoryAttribute()
    {
        return $this->revisionsHistory()->with('user', 'fixedByUser')->get();
    }

    public function getApprovalsHistoryAttribute()
    {
        return $this->approvalsHistory()->with('user')->get();
    }

    public function getAssociatesAttribute()
    {
        // Build a name lookup from approval history (most reliable — who actually performed each stage)
        // Use the eager-loaded relation if available to avoid N+1
        $historyNames = [];
        $stageToKey = [
            'Writer' => 'writer', 'Assignee' => 'writer', 'Writer Review' => 'writer',
            'Approver' => 'approver', 'Approver Review' => 'approver', 'Further Approver' => 'further_approver',
            'Brand Manager' => 'brand_manager', 'AM/BD' => 'brand_manager', 'Final Approval' => 'brand_manager',
            'Coordinator' => 'coordinator',
            'Designer' => 'designer',
        ];
        $approvals = $this->relationLoaded('approvalsHistory')
            ? $this->getRelation('approvalsHistory')
            : collect();
        foreach ($approvals as $approval) {
            $key = $stageToKey[$approval->stage] ?? null;
            if ($key && $approval->user && !isset($historyNames[$key])) {
                $historyNames[$key] = $approval->user->name;
            }
        }

        return [
            'writer'        => $this->writer?->name        ?: ($this->project?->writer?->name        ?: ($historyNames['writer']        ?? 'None')),
            'approver'      => $this->approver?->name      ?: ($this->project?->approver?->name      ?: ($historyNames['approver']      ?? 'None')),
            'further_approver' => $this->furtherApprover?->name ?: ($historyNames['further_approver'] ?? 'None'),
            'brand_manager' => $this->brandManager?->name  ?: ($this->project?->brandManager?->name  ?: ($historyNames['brand_manager'] ?? 'None')),
            'coordinator'   => $this->coordinator?->name   ?: ($this->project?->coordinator?->name   ?: ($historyNames['coordinator']   ?? 'None')),
            'designer'      => $this->designer?->name      ?: ($this->project?->designer?->name      ?: ($historyNames['designer']      ?? 'None')),
        ];
    }

    /**
     * Get the user who should be notified for a specific stage.
     */
    public function getRequiredFieldForStage($stage)
    {
        return $this->getWorkflow()->getRequiredFieldForStage($stage);
    }

    /**
     * Get the user who should be notified for a specific stage.
     */
    public function getNotifyTarget($stage)
    {
        return $this->getWorkflow()->getNotifyTarget($this, $stage);
    }

    /**
     * Send a stage change notification.
     */
    public function notifyStageChange($oldStage, $newStage, $actor)
    {
        $target = $this->getNotifyTarget($newStage);
        
        // In this workspace, if the user is testing alone, they might expect to notify themselves.
        // Or at least ensure someone gets notified.
        if ($target) {
            try {
                $target->notify(new \App\Notifications\DeliverableUpdated(
                    $this, 
                    "advanced the deliverable from **{$oldStage}** to **{$newStage}**", 
                    'stage_update', 
                    $actor
                ));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to send DeliverableUpdated notification: ' . $e->getMessage());
            }
        }
    }

    /**
     * Artwork review links relationship
     */
    public function artworkReviews()
    {
        return $this->hasMany(ArtworkReview::class, 'deliverable_id');
    }
}

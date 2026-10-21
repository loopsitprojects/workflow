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
        'flow_type',
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
        'reference_uploaded_by',
        'notes',
        'work_hours',
        'client_status',
        'designer_deadline',
        'revision_deadline',
        'artwork_uploaded_by',
    ];

    protected $casts = [
        'designer_deadline' => 'datetime',
        'revision_deadline' => 'datetime',
    ];

    protected $appends = [
        'reference_files_list',
        'reference_urls_list',
        'final_designs_list',
        'final_designs_urls_list',
        'workflow_stages',
        'can_remove_reference',
        'can_remove_artwork',
        'is_direct_design',
        'is_other_deliverable',
    ];

    public static function parseMediaArray(mixed $raw): array
    {
        if (empty($raw)) {
            return [];
        }
        if (is_array($raw)) {
            $flat = [];
            foreach ($raw as $r) {
                $sub = static::parseMediaArray($r);
                foreach ($sub as $s) {
                    $flat[] = $s;
                }
            }
            return array_values(array_filter($flat));
        }

        if (!is_string($raw)) {
            return [];
        }

        $val = trim($raw);
        if ($val === '') {
            return [];
        }

        // Decode HTML entities if present (e.g. &quot;, &#34;)
        if (str_contains($val, '&quot;') || str_contains($val, '&#')) {
            $val = html_entity_decode($val, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        // Strip wrapping quotes
        $val = trim($val, '"\'');

        // Loop up to 3 times to unwrap double/triple JSON encoding or escaped strings
        for ($i = 0; $i < 3; $i++) {
            $valTrim = trim($val, '"\'');
            if ((str_starts_with($valTrim, '[') && str_ends_with($valTrim, ']')) || (str_starts_with($valTrim, '{') && str_ends_with($valTrim, '}'))) {
                $decoded = json_decode($valTrim, true);
                if ($decoded === null) {
                    $stripped = stripslashes($valTrim);
                    $decoded = json_decode($stripped, true);
                }
                if (is_array($decoded)) {
                    $flat = [];
                    foreach ($decoded as $item) {
                        $sub = static::parseMediaArray($item);
                        foreach ($sub as $s) {
                            $flat[] = $s;
                        }
                    }
                    return array_values(array_filter($flat));
                }
                if (is_string($decoded)) {
                    $val = $decoded;
                    continue;
                }
            }
            break;
        }

        // If after all attempts $val still starts with [ and ends with ], extract URLs via regex
        if (str_starts_with($val, '[') && str_ends_with($val, ']')) {
            preg_match_all('/https?:\/\/[^\s"\'\\,\\]]+|\/[a-zA-Z0-9_\-\.\/]+/i', $val, $matches);
            if (!empty($matches[0])) {
                return array_values(array_unique(array_filter(array_map(fn($u) => trim($u, '"\'\\'), $matches[0]))));
            }
            return []; // Safeguard: Never return an unparsed JSON bracketed string as an image URL
        }

        // Split on commas or newlines if it's a comma-separated list
        $urls = preg_split('/[\r\n,]+/', $val);
        $cleaned = [];
        foreach ($urls as $u) {
            $uClean = trim($u, '"\'\\ ');
            if ($uClean === '[' || $uClean === ']' || $uClean === '') {
                continue;
            }
            $cleaned[] = $uClean;
        }
        return array_values(array_filter($cleaned));
    }

    public function getReferenceFilesArray(): array
    {
        return static::parseMediaArray($this->reference_file);
    }

    public function getReferenceUrlsArray(): array
    {
        return static::parseMediaArray($this->reference);
    }

    public function getFinalDesignsArray(): array
    {
        return static::parseMediaArray($this->final_designs);
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
        return static::parseMediaArray($this->final_designs_link);
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
        'Final Approval',
        'Closed'
    ];

    const OTHER_DELIVERABLE_STAGES = [
        'Assign',
        'Approve',
        'Close'
    ];

    const DIRECT_DESIGN_STAGES = [
        'Designer',
        'Writer Review',
        'Approver Review',
        'Further Approver',
        'Final Approval',
        'Closed'
    ];

    public function isDirectDesign(): bool
    {
        return ($this->flow_type ?? null) === 'direct_design' || ($this->parent?->flow_type ?? null) === 'direct_design';
    }

    public function isOtherDeliverable(): bool
    {
        if ($this->isDirectDesign()) return false;
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

        $assignedName = $this->assignee_name
            ?: ($this->writer?->name ?: ($this->project?->writer?->name ?: ($historyNames['writer'] ?? 'None')));

        return [
            'writer'        => $this->writer?->name        ?: ($this->project?->writer?->name        ?: ($historyNames['writer']        ?? 'None')),
            'assignee'      => $assignedName,
            'approver'      => $this->approver?->name      ?: ($this->project?->approver?->name      ?: ($historyNames['approver']      ?? 'None')),
            'further_approver' => $this->furtherApprover?->name ?: ($historyNames['further_approver'] ?? 'None'),
            'brand_manager' => $this->brandManager?->name  ?: ($this->project?->brandManager?->name  ?: ($historyNames['brand_manager'] ?? 'None')),
            'coordinator'   => $this->coordinator?->name   ?: ($this->project?->coordinator?->name   ?: ($historyNames['coordinator']   ?? 'None')),
            'designer'      => $this->designer?->name      ?: ($this->project?->designer?->name      ?: ($historyNames['designer']      ?? 'None')),
        ];
    }

    public function getIsOtherDeliverableAttribute(): bool
    {
        return $this->isOtherDeliverable();
    }

    public function getIsDirectDesignAttribute(): bool
    {
        return $this->isDirectDesign();
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

    public function referenceUploader()
    {
        return $this->belongsTo(User::class, 'reference_uploaded_by');
    }

    public function artworkUploader()
    {
        return $this->belongsTo(User::class, 'artwork_uploaded_by');
    }

    public function canUserRemoveReference(?User $user = null): bool
    {
        $user = $user ?? auth()->user();
        if (!$user) {
            return false;
        }
        if ($user->isAdmin()) {
            return true;
        }
        if ($this->reference_uploaded_by && (int)$user->id === (int)$this->reference_uploaded_by) {
            return true;
        }
        $creatorId = $this->brand_manager_id ?? $this->project?->brand_manager_id;
        if ($creatorId && (int)$user->id === (int)$creatorId) {
            return true;
        }
        if ($this->project && (int)$user->id === (int)$this->project->brand_manager_id) {
            return true;
        }
        return false;
    }

    public function canUserRemoveArtwork(?User $user = null): bool
    {
        $user = $user ?? auth()->user();
        if (!$user) {
            return false;
        }
        if ($user->isAdmin()) {
            return true;
        }
        if ($this->artwork_uploaded_by && (int)$user->id === (int)$this->artwork_uploaded_by) {
            return true;
        }
        // If artwork_uploaded_by is not set, or deliverable is in Designer stage, allow the assigned designer
        if ($this->designer_id && (int)$user->id === (int)$this->designer_id) {
            if (!$this->artwork_uploaded_by || $this->approval_stage === 'Designer') {
                return true;
            }
        }
        // Brand manager or project creator
        $creatorId = $this->brand_manager_id ?? $this->project?->brand_manager_id;
        if ($creatorId && (int)$user->id === (int)$creatorId) {
            return true;
        }
        if ($this->project && (int)$user->id === (int)$this->project->brand_manager_id) {
            return true;
        }
        return false;
    }

    public function getCanRemoveReferenceAttribute(): bool
    {
        return $this->canUserRemoveReference();
    }

    public function getCanRemoveArtworkAttribute(): bool
    {
        return $this->canUserRemoveArtwork();
    }
}

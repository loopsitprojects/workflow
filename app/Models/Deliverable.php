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
        'designer_deadline',
    ];

    protected $casts = [
        'designer_deadline' => 'datetime',
    ];

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
        'Closed'
    ];

    const CAMPAIGN_STAGES = [
        'Assignee',
        'AM/BD',
        'Final Approval',
        'Closed'
    ];

    public function getStages()
    {
        if ($this->project && in_array($this->project->workflow_type, ['campaign', 'pitch'])) {
            return self::CAMPAIGN_STAGES;
        }
        return self::STAGES;
    }

    public function getStageProgress()
    {
        $stages = $this->getStages();
        $index = array_search($this->approval_stage ?? $stages[0], $stages);
        if ($index === false) return 0;
        
        $count = count($stages);
        if ($count === 4) {
            $milestones = [10, 50, 90, 100];
            return $milestones[$index] ?? 0;
        }
        
        // 10-stage retainer workflow (with Further Approver)
        if ($count === 10) {
            $milestones = [0, 10, 20, 32, 47, 60, 72, 84, 93, 100];
            return $milestones[$index] ?? 0;
        }
        $milestones = [0, 10, 25, 40, 55, 68, 80, 92, 100];
        return $milestones[$index] ?? 0;
    }

    public function getNextStage()
    {
        $stages = $this->getStages();
        $currentIndex = array_search($this->approval_stage ?? $stages[0], $stages);
        if ($currentIndex !== false && $currentIndex < count($stages) - 1) {
            return $stages[$currentIndex + 1];
        }
        return null;
    }

    public function getPrevStage()
    {
        $stages = $this->getStages();
        $currentIndex = array_search($this->approval_stage ?? $stages[0], $stages);
        if ($currentIndex !== false && $currentIndex > 0) {
            return $stages[$currentIndex - 1];
        }
        return null;
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
            'Approver' => 'approver', 'Approver Review' => 'approver', 'Further Approver' => 'approver',
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
        return match ($stage) {
            'Writer', 'Assignee', 'Writer Review'          => 'writer_id',
            'Approver', 'Approver Review', 'Further Approver' => 'approver_id',
            'Brand Manager', 'AM/BD', 'Final Approval'        => 'brand_manager_id',
            'Coordinator'        => 'coordinator_id',
            'Designer'           => 'designer_id',
            default              => null,
        };
    }

    /**
     * Get the user who should be notified for a specific stage.
     */
    public function getNotifyTarget($stage)
    {
        $target = match ($stage) {
            'Approver', 'Approver Review', 'Further Approver' => $this->approver ?? $this->project?->approver,
            'Brand Manager'  => $this->brandManager ?? $this->project?->brandManager,
            'Coordinator'    => $this->coordinator ?? $this->project?->coordinator,
            'Designer'       => $this->designer ?? $this->project?->designer,
            'Final Approval' => $this->brandManager ?? $this->project?->brandManager,
            'AM/BD'          => $this->brandManager ?? $this->project?->brandManager,
            'Assignee', 'Writer Review' => $this->writer ?? $this->project?->writer,
            'Closed'         => $this->writer ?? $this->project?->writer,
            default          => null,
        };

        // Fallback to Project Lead if stage target is unassigned
        if (!$target) {
            $target = $this->project?->lead;
        }

        // Final fallback to any Admin/Owner if still no target
        if (!$target) {
            $target = \App\Models\User::where('role', 'Admin')->first();
        }

        return $target;
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
            $target->notify(new \App\Notifications\DeliverableUpdated(
                $this, 
                "advanced the deliverable from **{$oldStage}** to **{$newStage}**", 
                'stage_update', 
                $actor
            ));
        }
    }
}

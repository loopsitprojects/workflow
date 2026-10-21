<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliverableRevision extends Model
{
    protected $with = ['user', 'fixedByUser'];
    protected $appends = ['target_role'];

    protected $fillable = [
        'deliverable_id',
        'user_id',
        'instructions',
        'image_path',
        'stage_at_revision',
        'revision_deadline',
        'target',
        'fixed_by_user_id',
        'fixed_at',
    ];

    protected $casts = [
        'revision_deadline' => 'datetime',
        'fixed_at' => 'datetime',
    ];

    public function getTargetRoleAttribute(): string
    {
        if (!empty($this->target)) {
            return strtolower($this->target);
        }

        $stage = $this->stage_at_revision ?? '';
        if (in_array($stage, ['Writer', 'Approver', 'Further Approver', 'Brand Manager', 'Coordinator', 'Assignee', 'Assign'])) {
            return 'writer';
        }
        if ($stage === 'Writer Review') {
            return 'designer';
        }

        return 'designer';
    }

    public function fixedByUser()
    {
        return $this->belongsTo(User::class, 'fixed_by_user_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deliverable()
    {
        return $this->belongsTo(Deliverable::class);
    }
}

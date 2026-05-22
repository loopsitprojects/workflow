<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'brand_id',
        'job_number',
        'name',
        'description',
        'lead_name',
        'status',
        'deadline',
        'progress',
        'priority',
        'type',
        'workflow_type',
        'writer_id',
        'approver_id',
        'brand_manager_id',
        'designer_id',
        'coordinator_id',
        'sub_type',
        'brief_file_path',
        'lead_id',
    ];

    public function lead()
    {
        return $this->belongsTo(User::class, 'lead_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function deliverables()
    {
        return $this->hasMany(Deliverable::class);
    }

    public function writer()
    {
        return $this->belongsTo(User::class, 'writer_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function brandManager()
    {
        return $this->belongsTo(User::class, 'brand_manager_id');
    }

    public function designer()
    {
        return $this->belongsTo(User::class, 'designer_id');
    }

    public function coordinator()
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class);
    }
}

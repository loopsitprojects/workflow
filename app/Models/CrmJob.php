<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmJob extends Model
{
    protected $fillable = [
        'crm_job_id',
        'brand_name',
        'brand_id',
        'title',
        'deadline',
        'status',
        'project_id',
        'payload',
    ];

    protected $casts = [
        'deadline' => 'date',
        'payload'  => 'array',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}

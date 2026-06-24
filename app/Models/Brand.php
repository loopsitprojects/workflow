<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    public function getRouteKeyName()
    {
        return 'slug';
    }

    protected $fillable = [
        'name',
        'slug',
        'logo_url',
        'location',
        'description',
        'active_projects',
        'total_members',
        'overall_progress',
        'health_score',
        'milestones_met',
        'revenue_impact',
        'current_lead',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function deliverables()
    {
        return $this->hasManyThrough(Deliverable::class, Project::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class);
    }
}

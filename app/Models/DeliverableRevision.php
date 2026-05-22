<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliverableRevision extends Model
{
    protected $fillable = [
        'deliverable_id',
        'user_id',
        'instructions',
        'stage_at_revision',
        'fixed_by_user_id',
        'fixed_at',
    ];

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

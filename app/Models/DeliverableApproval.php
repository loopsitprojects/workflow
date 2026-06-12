<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliverableApproval extends Model
{
    protected $fillable = [
        'deliverable_id',
        'user_id',
        'stage',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deliverable()
    {
        return $this->belongsTo(Deliverable::class);
    }
}

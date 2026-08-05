<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliverableReassignment extends Model
{
    protected $fillable = [
        'deliverable_id',
        'role',
        'from_user_id',
        'to_user_id',
        'reassigned_by_user_id',
        'reason',
    ];

    public function deliverable()
    {
        return $this->belongsTo(Deliverable::class);
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function reassignedBy()
    {
        return $this->belongsTo(User::class, 'reassigned_by_user_id');
    }
}

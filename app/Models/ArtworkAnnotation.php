<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArtworkAnnotation extends Model
{
    protected $fillable = [
        'artwork_review_id',
        'type',
        'x_percent',
        'y_percent',
        'content',
        'color',
        'pin_number',
        'is_resolved',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
        'x_percent'   => 'float',
        'y_percent'   => 'float',
        'pin_number'  => 'integer',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function review()
    {
        return $this->belongsTo(ArtworkReview::class, 'artwork_review_id');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}

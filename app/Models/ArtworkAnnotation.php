<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArtworkAnnotation extends Model
{
    protected $fillable = [
        'artwork_review_id',
        'artwork_index',
        'image_url',
        'type',
        'x_percent',
        'y_percent',
        'content',
        'response_text',
        'responded_by',
        'responded_at',
        'color',
        'pin_number',
        'is_resolved',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'is_resolved'   => 'boolean',
        'resolved_at'   => 'datetime',
        'responded_at'  => 'datetime',
        'x_percent'     => 'float',
        'y_percent'     => 'float',
        'pin_number'    => 'integer',
        'artwork_index' => 'integer',
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

    public function respondedBy()
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    public function comments()
    {
        return $this->hasMany(ArtworkAnnotationComment::class, 'artwork_annotation_id')->with('user')->oldest();
    }
}

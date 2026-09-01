<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArtworkAnnotationComment extends Model
{
    protected $fillable = [
        'artwork_annotation_id',
        'user_id',
        'comment',
    ];

    public function annotation()
    {
        return $this->belongsTo(ArtworkAnnotation::class, 'artwork_annotation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

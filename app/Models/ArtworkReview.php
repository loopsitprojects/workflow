<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ArtworkReview extends Model
{
    protected $fillable = [
        'deliverable_id',
        'token',
        'client_name',
        'client_email',
        'expires_at',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active'  => 'boolean',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function deliverable()
    {
        return $this->belongsTo(Deliverable::class);
    }

    public function annotations()
    {
        return $this->hasMany(ArtworkAnnotation::class)->orderBy('created_at');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Generate a cryptographically safe unique token.
     */
    public static function generateToken(): string
    {
        do {
            $token = Str::random(64);
        } while (self::where('token', $token)->exists());

        return $token;
    }

    /**
     * Determine whether this review link is currently accessible.
     */
    public function isAccessible(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Count unresolved annotations.
     */
    public function unresolvedCount(): int
    {
        return $this->annotations()->where('is_resolved', false)->count();
    }
}

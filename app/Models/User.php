<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'job_title',
        'google_id',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Determine if the user has the admin role.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['Admin', 'Owner']);
    }

    /**
     * Determine if the user has a managerial role.
     * Managers can see brand-wise and project-wise reports.
     *
     * @return bool
     */
    public function isManager(): bool
    {
        return in_array($this->role, ['Admin', 'Owner', 'Operations Manager', 'Brand Manager']);
    }

    public function getAvatarUrlAttribute()
    {
        if (!empty($this->avatar)) {
            return $this->avatar;
        }
        return "https://ui-avatars.com/api/?name=" . urlencode($this->name) . "&background=FFEDD5&color=C2410C";
    }

    public function brands()
    {
        return $this->belongsToMany(Brand::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }
}

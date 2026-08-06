<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'role',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}

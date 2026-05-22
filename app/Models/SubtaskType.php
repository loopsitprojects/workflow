<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubtaskType extends Model
{
    protected $fillable = ['name', 'workflow_type'];
}

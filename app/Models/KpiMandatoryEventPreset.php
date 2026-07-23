<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiMandatoryEventPreset extends Model
{
    protected $fillable = ['name', 'department_ids', 'user_ids'];

    protected $casts = [
        'department_ids' => 'array',
        'user_ids' => 'array',
    ];
}

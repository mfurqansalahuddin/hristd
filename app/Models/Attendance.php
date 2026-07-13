<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'user_id', 'date', 'clock_in', 'clock_out',
        'is_apel', 'status', 'late_reason', 'supervisor_approval',
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'is_apel' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

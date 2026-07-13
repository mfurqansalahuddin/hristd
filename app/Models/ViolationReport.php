<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViolationReport extends Model
{
    protected $fillable = [
        'reported_user_id', 'reporter_id', 'period_id', 'category',
        'description', 'photo_path', 'incident_date', 'status', 'deduction_point',
    ];

    protected $casts = [
        'incident_date' => 'date',
    ];

    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function period()
    {
        return $this->belongsTo(KpiPeriod::class, 'period_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyActivity extends Model
{
    protected $fillable = [
        'user_id', 'kpi_plan_id', 'activity_date', 'description', 'photo_path',
    ];

    protected $casts = [
        'activity_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kpiPlan()
    {
        return $this->belongsTo(KpiPlan::class);
    }
}

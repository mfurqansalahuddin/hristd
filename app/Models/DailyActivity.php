<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyActivity extends Model
{
    protected $fillable = [
        'user_id', 'kpi_plan_id', 'activity_date', 'description', 'photo_path',
    ];

    protected $casts = [
        // date:Y-m-d supaya JSON-nya '2026-07-17', bukan '2026-07-16T17:00:00Z' (geser -7 jam dari Asia/Jakarta).
        'activity_date' => 'date:Y-m-d',
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

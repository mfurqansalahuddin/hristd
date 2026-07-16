<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiPlanReview extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['period_id', 'user_id', 'reviewer_id', 'kpi_plan_id', 'action', 'comment'];

    public function period()
    {
        return $this->belongsTo(KpiPeriod::class, 'period_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function kpiPlan()
    {
        return $this->belongsTo(KpiPlan::class);
    }
}

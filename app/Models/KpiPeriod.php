<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiPeriod extends Model
{
    protected $fillable = ['month', 'year', 'status'];

    public function kpiPlans()
    {
        return $this->hasMany(KpiPlan::class, 'period_id');
    }

    public function violationReports()
    {
        return $this->hasMany(ViolationReport::class, 'period_id');
    }

    public function kpiDisputes()
    {
        return $this->hasMany(KpiDispute::class, 'period_id');
    }

    public function kpiFinalScores()
    {
        return $this->hasMany(KpiFinalScore::class, 'period_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiEvaluation extends Model
{
    protected $fillable = ['kpi_plan_id', 'evaluator_id', 'evaluator_role', 'score', 'note'];

    public function kpiPlan()
    {
        return $this->belongsTo(KpiPlan::class);
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }
}

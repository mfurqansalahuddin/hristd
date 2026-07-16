<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiExtraCriterionScore extends Model
{
    protected $fillable = ['kpi_extra_criterion_id', 'user_id', 'period_id', 'evaluator_id', 'evaluator_role', 'score', 'reason'];

    public function criterion()
    {
        return $this->belongsTo(KpiExtraCriterion::class, 'kpi_extra_criterion_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }
}

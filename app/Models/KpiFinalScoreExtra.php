<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiFinalScoreExtra extends Model
{
    protected $fillable = ['kpi_final_score_id', 'kpi_extra_criterion_id', 'score'];

    public function finalScore()
    {
        return $this->belongsTo(KpiFinalScore::class, 'kpi_final_score_id');
    }

    public function criterion()
    {
        return $this->belongsTo(KpiExtraCriterion::class, 'kpi_extra_criterion_id');
    }
}

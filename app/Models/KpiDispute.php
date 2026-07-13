<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiDispute extends Model
{
    protected $fillable = [
        'period_id', 'user_id', 'kpi_evaluation_id', 'reason',
        'evidence_file_path', 'status', 'resolution_note', 'revised_score',
    ];

    public function period()
    {
        return $this->belongsTo(KpiPeriod::class, 'period_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kpiEvaluation()
    {
        return $this->belongsTo(KpiEvaluation::class);
    }
}

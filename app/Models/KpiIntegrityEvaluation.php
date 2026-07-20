<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiIntegrityEvaluation extends Model
{
    protected $fillable = [
        'evaluator_id', 'reported_user_id', 'period_id', 'kpi_integrity_category_id',
        'evaluator_role', 'decision', 'description', 'photo_path',
    ];

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function period()
    {
        return $this->belongsTo(KpiPeriod::class, 'period_id');
    }

    public function category()
    {
        return $this->belongsTo(KpiIntegrityCategory::class, 'kpi_integrity_category_id');
    }
}

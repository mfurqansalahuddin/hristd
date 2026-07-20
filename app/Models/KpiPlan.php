<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiPlan extends Model
{
    protected $fillable = [
        'user_id', 'period_id', 'name', 'target_description', 'weight',
        'self_assessment_score', 'self_assessment_note', 'self_assessment_photo_path', 'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function period()
    {
        return $this->belongsTo(KpiPeriod::class, 'period_id');
    }

    public function evaluations()
    {
        return $this->hasMany(KpiEvaluation::class);
    }
}

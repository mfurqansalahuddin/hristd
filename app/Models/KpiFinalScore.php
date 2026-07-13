<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiFinalScore extends Model
{
    protected $fillable = [
        'user_id', 'period_id', 'score_kinerja', 'score_kehadiran',
        'score_apel', 'score_pakaian', 'score_integritas', 'grand_total_score',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function period()
    {
        return $this->belongsTo(KpiPeriod::class, 'period_id');
    }
}

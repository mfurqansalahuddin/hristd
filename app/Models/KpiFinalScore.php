<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiFinalScore extends Model
{
    protected $fillable = [
        'user_id', 'period_id', 'score_kinerja', 'score_kehadiran',
        'score_apel', 'score_pakaian', 'score_integritas', 'grand_total_score',
    ];

    /** @var array<string, string> label predikat => warna x-ui.badge */
    public const PREDIKAT_COLORS = [
        'Tidak Memuaskan' => 'error',
        'Kurang Memuaskan' => 'warning',
        'Rata-rata' => 'light',
        'Memuaskan' => 'info',
        'Sangat Memuaskan' => 'success',
    ];

    /** Kategori pencapaian dari grand_total_score (panduan predikat: 0-50/51-65/66-75/76-90/>90). */
    public function predikat(): string
    {
        $score = (float) $this->grand_total_score;

        return match (true) {
            $score > 90 => 'Sangat Memuaskan',
            $score >= 76 => 'Memuaskan',
            $score >= 66 => 'Rata-rata',
            $score >= 51 => 'Kurang Memuaskan',
            default => 'Tidak Memuaskan',
        };
    }

    public function predikatColor(): string
    {
        return self::PREDIKAT_COLORS[$this->predikat()];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function period()
    {
        return $this->belongsTo(KpiPeriod::class, 'period_id');
    }

    public function extras()
    {
        return $this->hasMany(KpiFinalScoreExtra::class);
    }
}

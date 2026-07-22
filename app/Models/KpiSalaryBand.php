<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiSalaryBand extends Model
{
    protected $fillable = ['min_score', 'percentage'];

    protected function casts(): array
    {
        return [
            'min_score' => 'decimal:2',
            'percentage' => 'integer',
        ];
    }

    /**
     * Persentase gaji dari grand_total_score: ambang tertinggi yang terlampaui
     * (skor > min_score) menang; band dengan min_score null jadi catch-all
     * (skor <= ambang terendah yang ada).
     *
     * @param  iterable<int, array{min_score: numeric-string|float|null, percentage: int}>  $bands
     */
    public static function percentageFor(float $score, iterable $bands): int
    {
        $ordered = collect($bands)->sortByDesc(fn ($band) => $band['min_score'] ?? -INF);

        foreach ($ordered as $band) {
            if ($band['min_score'] === null || $score > (float) $band['min_score']) {
                return (int) $band['percentage'];
            }
        }

        return 0;
    }
}

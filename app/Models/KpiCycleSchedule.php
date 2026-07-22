<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiCycleSchedule extends Model
{
    protected $table = 'kpi_cycle_schedule';

    protected $fillable = ['phase', 'start_day', 'end_day'];

    public const LABELS = [
        'PENGINGAT_DIBUKA' => 'Pengingat Dibuka',
        'PENILAIAN' => 'Masa Penilaian',
        'REVIEW_VALIDASI' => 'Review & Validasi',
        'FINALISASI' => 'Finalisasi',
    ];

    public function label(): string
    {
        return self::LABELS[$this->phase] ?? $this->phase;
    }

    /** Fase mana yang seharusnya berlaku hari ini menurut master jadwal (murni pembanding, read-only). */
    public static function phaseForDay(int $day): ?self
    {
        return self::where('start_day', '<=', $day)->where('end_day', '>=', $day)->first();
    }
}

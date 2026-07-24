<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiPeriodPhase extends Model
{
    protected $fillable = ['kpi_period_id', 'phase', 'start_date', 'end_date'];

    protected function casts(): array
    {
        // date:Y-m-d supaya JSON-nya '2026-07-17', bukan '2026-07-16T17:00:00Z' (geser -7 jam dari Asia/Jakarta).
        return ['start_date' => 'date:Y-m-d', 'end_date' => 'date:Y-m-d'];
    }

    public const LABELS = [
        'PENGINGAT_DIBUKA' => 'Pengingat Dibuka',
        'PENILAIAN' => 'Masa Penilaian',
        'REVIEW_VALIDASI' => 'Review & Validasi',
        'FINALISASI' => 'Finalisasi',
    ];

    /** @var list<string> Urutan fase tetap, dipakai untuk auto-create baris saat periode baru dibuka. */
    public const PHASES = ['PENGINGAT_DIBUKA', 'PENILAIAN', 'REVIEW_VALIDASI', 'FINALISASI'];

    public function label(): string
    {
        return self::LABELS[$this->phase] ?? $this->phase;
    }

    public function period()
    {
        return $this->belongsTo(KpiPeriod::class, 'kpi_period_id');
    }

    /** Fase yang tanggalnya mencakup hari ini, untuk periode tsb. Null kalau tanggal belum diisi admin. */
    public static function currentFor(?KpiPeriod $period): ?self
    {
        if (! $period) {
            return null;
        }

        $today = now()->toDateString();

        return self::where('kpi_period_id', $period->id)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->first();
    }

    /** Sisa hari sampai fase ini berakhir (negatif kalau sudah lewat). */
    public function daysRemaining(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->end_date->copy()->startOfDay(), false);
    }

    /** Banner peringatan tampil mulai H-3 sebelum fase berakhir sampai hari terakhirnya. */
    public function showBanner(): bool
    {
        return $this->daysRemaining() >= 0 && $this->daysRemaining() <= 3;
    }
}

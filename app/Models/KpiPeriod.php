<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiPeriod extends Model
{
    protected $fillable = ['month', 'year', 'status'];

    /** Periode "berjalan" = yang paling baru dibuka (year, month terbesar). */
    public static function current(): ?self
    {
        return self::orderByDesc('year')->orderByDesc('month')->first();
    }

    public function previous(): ?self
    {
        [$year, $month] = $this->month === 1 ? [$this->year - 1, 12] : [$this->year, $this->month - 1];

        return self::where('year', $year)->where('month', $month)->first();
    }

    public function kpiPlans()
    {
        return $this->hasMany(KpiPlan::class, 'period_id');
    }

    public function violationReports()
    {
        return $this->hasMany(ViolationReport::class, 'period_id');
    }

    public function kpiDisputes()
    {
        return $this->hasMany(KpiDispute::class, 'period_id');
    }

    public function kpiFinalScores()
    {
        return $this->hasMany(KpiFinalScore::class, 'period_id');
    }
}

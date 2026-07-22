<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiPeriod extends Model
{
    protected $fillable = ['month', 'year', 'status', 'weights_snapshot'];

    protected function casts(): array
    {
        return ['weights_snapshot' => 'array'];
    }

    /**
     * Salin kpi_component_weights + kpi_evaluator_weights + kpi_integrity_source_weights
     * + kpi_salary_bands begitu periode dibuat — dibekukan untuk periode ini saja, supaya
     * edit master data belakangan tidak ikut mengubah periode yang sudah berjalan/CLOSED
     * (plan.md §13, bobot ini terbukti sering berubah).
     */
    protected static function booted(): void
    {
        static::creating(function (self $period) {
            $period->weights_snapshot ??= self::buildWeightsSnapshot();
        });
    }

    /** @return array{component_weights: array<string,int>, evaluator_weights: array<int,array<string,int>>, integrity_source_weights: array<string,int>, salary_bands: list<array{min_score: float|null, percentage: int}>} */
    public static function buildWeightsSnapshot(): array
    {
        return [
            'component_weights' => KpiComponentWeight::pluck('weight', 'component')->all(),
            'evaluator_weights' => KpiEvaluatorWeight::all()
                ->groupBy('job_level')
                ->map(fn ($rows) => $rows->pluck('weight', 'slot')->all())
                ->all(),
            'integrity_source_weights' => KpiIntegritySourceWeight::pluck('weight', 'source')->all(),
            'salary_bands' => KpiSalaryBand::orderByDesc('min_score')->get(['min_score', 'percentage'])
                ->map(fn (KpiSalaryBand $band) => [
                    'min_score' => $band->min_score !== null ? (float) $band->min_score : null,
                    'percentage' => $band->percentage,
                ])
                ->all(),
        ];
    }

    /** Fallback ke tabel master live kalau snapshot kosong (periode lama, sebelum kolom ini ada). */
    private function snapshot(): array
    {
        return $this->weights_snapshot ?? self::buildWeightsSnapshot();
    }

    public function componentWeight(string $component, int $default = 0): int
    {
        return (int) ($this->snapshot()['component_weights'][$component] ?? $default);
    }

    /** @return array<string, int> */
    public function evaluatorWeights(int $jobLevel): array
    {
        return $this->snapshot()['evaluator_weights'][$jobLevel] ?? [];
    }

    /** @return array<string, int> */
    public function integritySourceWeights(): array
    {
        return $this->snapshot()['integrity_source_weights'] ?? [];
    }

    public function salaryPercentageFor(float $grandTotalScore): int
    {
        return KpiSalaryBand::percentageFor($grandTotalScore, $this->snapshot()['salary_bands'] ?? []);
    }

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

    /** Rencana kerja (create/update/submit) + approval atasan pertama dibuka. */
    public function isPlanningOpen(): bool
    {
        return $this->status === 'DRAFT';
    }

    /** Penilaian evaluator dibuka. */
    public function isScoringOpen(): bool
    {
        return $this->status === 'EVALUATION';
    }

    /** Self-assessment dibuka: fase Working (rencana terkunci, penilai belum bisa menilai) dan Evaluation. */
    public function isSelfAssessmentOpen(): bool
    {
        return in_array($this->status, ['WORKING', 'EVALUATION'], true);
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

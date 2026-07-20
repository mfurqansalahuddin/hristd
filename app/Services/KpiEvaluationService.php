<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\KpiComponentWeight;
use App\Models\KpiEvaluatorWeight;
use App\Models\KpiExtraCriterion;
use App\Models\KpiExtraCriterionScore;
use App\Models\KpiFinalScore;
use App\Models\KpiIntegrityCategory;
use App\Models\KpiIntegrityEvaluation;
use App\Models\KpiIntegritySourceWeight;
use App\Models\KpiPeriod;
use App\Models\KpiPlan;
use App\Models\User;
use App\Models\ViolationReport;

/**
 * Menghitung kpi_final_scores dari 5 bucket tetap (plan.md §7) plus kriteria
 * penilaian tambahan aktif (`kpi_extra_criteria`, 2026-07-16) saat periode
 * ditutup (Fase C). Dipanggil dari KpiPeriodsTable::updateStatus() saat
 * status -> CLOSED.
 *
 * ponytail: dijalankan sinkron per periode (job kecil, ~ratusan pegawai) —
 * pindah ke queued job kalau jumlah pegawai membesar jauh dari skala saat ini.
 */
class KpiEvaluationService
{
    /** Level jabatan yang masuk cakupan KPI bulanan — Direksi di luar cakupan (§5.2). */
    private const EVALUATED_JOB_LEVELS = [2, 3, 4];

    /** Deduksi flat per aduan Pakaian Dinas tervalidasi kalau `deduction_point` belum diisi manual (§7 poin 4, dikonfirmasi user 2026-07-20). */
    private const DEFAULT_PAKAIAN_DEDUCTION = 5;

    public function calculateForPeriod(KpiPeriod $period): void
    {
        $weights = KpiComponentWeight::pluck('weight', 'component');

        User::whereIn('job_level', self::EVALUATED_JOB_LEVELS)->each(function (User $user) use ($period, $weights) {
            $this->calculateForUser($user, $period, $weights);
        });
    }

    public function calculateForUser(User $user, KpiPeriod $period, ?\Illuminate\Support\Collection $weights = null): KpiFinalScore
    {
        $weights ??= KpiComponentWeight::pluck('weight', 'component');

        $scoreKinerja = $this->kinerjaScore($user, $period, (int) ($weights['KINERJA'] ?? 50));
        $scoreKehadiran = $this->kehadiranScore($user, $period, (int) ($weights['KEHADIRAN'] ?? 20));
        $scoreApel = $this->apelScore($user, $period, (int) ($weights['APEL'] ?? 5));
        $scorePakaian = $this->pakaianScore($user, $period, (int) ($weights['PAKAIAN_DINAS'] ?? 5));
        $scoreIntegritas = $this->integritasScore($user, $period, (int) ($weights['INTEGRITAS'] ?? 20));

        $grandTotal = $scoreKinerja + $scoreKehadiran + $scoreApel + $scorePakaian + $scoreIntegritas;

        $extraScores = KpiExtraCriterion::where('is_active', true)->get()
            ->mapWithKeys(fn (KpiExtraCriterion $criterion) => [
                $criterion->id => $this->extraCriterionScore($user, $period, $criterion),
            ]);

        $grandTotal += $extraScores->sum();

        $finalScore = KpiFinalScore::updateOrCreate(
            ['user_id' => $user->id, 'period_id' => $period->id],
            [
                'score_kinerja' => $scoreKinerja,
                'score_kehadiran' => $scoreKehadiran,
                'score_apel' => $scoreApel,
                'score_pakaian' => $scorePakaian,
                'score_integritas' => $scoreIntegritas,
                'grand_total_score' => $grandTotal,
            ]
        );

        $finalScore->extras()->delete();
        foreach ($extraScores as $criterionId => $score) {
            $finalScore->extras()->create(['kpi_extra_criterion_id' => $criterionId, 'score' => $score]);
        }

        return $finalScore;
    }

    /**
     * Kriteria penilaian tambahan (di luar 5 bucket tetap, §2 catatan 2026-07-16):
     * rata-rata tertimbang skor evaluator (persis pola kinerjaScore()), lalu
     * diskalakan ke bobot kriteria itu sendiri.
     */
    private function extraCriterionScore(User $user, KpiPeriod $period, KpiExtraCriterion $criterion): float
    {
        $evaluatorWeights = KpiEvaluatorWeight::where('job_level', $user->job_level)->pluck('weight', 'slot');

        $scores = KpiExtraCriterionScore::where('kpi_extra_criterion_id', $criterion->id)
            ->where('user_id', $user->id)
            ->where('period_id', $period->id)
            ->get();

        $weightedScore = $scores->sum(function (KpiExtraCriterionScore $score) use ($evaluatorWeights) {
            $weight = $evaluatorWeights[$score->evaluator_role] ?? 0;

            return $score->score * $weight / 100;
        });

        return round(min($weightedScore, 100) / 100 * $criterion->weight, 2);
    }

    /**
     * §5: skor tiap target rencana kerja = rata-rata tertimbang skor 3 penilai
     * (bobot dari kpi_evaluator_weights), lalu dikalikan bobot item (weight,
     * skala 0-50) untuk dapat kontribusinya ke bucket Kinerja (50%).
     */
    private function kinerjaScore(User $user, KpiPeriod $period, int $componentWeight): float
    {
        $plans = KpiPlan::where('user_id', $user->id)
            ->where('period_id', $period->id)
            ->where('status', 'APPROVED')
            ->with('evaluations')
            ->get();

        if ($plans->isEmpty()) {
            return 0.0;
        }

        $evaluatorWeights = KpiEvaluatorWeight::where('job_level', $user->job_level)->pluck('weight', 'slot');

        $total = $plans->sum(function (KpiPlan $plan) use ($evaluatorWeights) {
            $planScore = $plan->evaluations->sum(function ($evaluation) use ($evaluatorWeights) {
                $weight = $evaluatorWeights[$evaluation->evaluator_role] ?? 0;

                return $evaluation->score * $weight / 100;
            });

            return $planScore * $plan->weight / 100;
        });

        // $total sudah dalam skala 0-50 (akumulasi bobot item rencana kerja per user, §7 poin 1);
        // component weight (default 50) dipakai sebagai plafon, bukan pengali ulang.
        return round(min($total, $componentWeight), 2);
    }

    /** §7 poin 2 & §8.2-8.4: rasio hari hadir (Cuti/Sakit/DL approved dianggap hadir 100%, bukan Alpa). */
    private function kehadiranScore(User $user, KpiPeriod $period, int $componentWeight): float
    {
        $workingDays = $this->workingDaysIn($period);

        if ($workingDays === 0) {
            return 0.0;
        }

        $hadirDays = Attendance::where('user_id', $user->id)
            ->whereYear('date', $period->year)
            ->whereMonth('date', $period->month)
            ->where('status', '!=', 'ALPA')
            ->count();

        return round(min($hadirDays / $workingDays, 1) * $componentWeight, 2);
    }

    /** §7 poin 3: rasio Senin ber-is_apel=true dibagi total Senin dalam periode. */
    private function apelScore(User $user, KpiPeriod $period, int $componentWeight): float
    {
        $totalSenin = collect($this->workingDaysDates($period))->filter(fn ($date) => $date->isMonday())->count();

        if ($totalSenin === 0) {
            return 0.0;
        }

        $apelCount = Attendance::where('user_id', $user->id)
            ->whereYear('date', $period->year)
            ->whereMonth('date', $period->month)
            ->where('is_apel', true)
            ->count();

        return round(min($apelCount / $totalSenin, 1) * $componentWeight, 2);
    }

    /**
     * §7 poin 4 & §8.4: default 100, dipotong aduan foto tervalidasi (dedup
     * harian — kategori sama + orang sama + hari sama dihitung 1x).
     */
    private function pakaianScore(User $user, KpiPeriod $period, int $componentWeight): float
    {
        $dedupedDays = ViolationReport::where('reported_user_id', $user->id)
            ->where('period_id', $period->id)
            ->where('category', 'PAKAIAN_DINAS')
            ->where('status', 'VALIDATED')
            ->get()
            ->unique(fn (ViolationReport $report) => $report->incident_date->toDateString());

        $deduction = $dedupedDays->sum(fn (ViolationReport $report) => $report->deduction_point ?: self::DEFAULT_PAKAIAN_DEDUCTION);

        return round(max(100 - $deduction, 0) / 100 * $componentWeight, 2);
    }

    /**
     * §7.1: Integritas kini 4 sumber berbobot (default 25/25/25/25,
     * `kpi_integrity_source_weights`) — Penilai 1/2/3 (review wajib per
     * kategori, `kpi_integrity_evaluations`) + Aduan Perusahaan (company-wide,
     * `violation_reports` category=INTEGRITAS). Tiap sumber dihitung skornya
     * sendiri lewat formula yang sama: 8 sub-kategori, tiap kategori mulai
     * dari skor penuh (`deduction_value`-nya sendiri), nol total kalau ada
     * temuan tervalidasi/KURANGIN di kategori itu dari sumber tsb. Lalu
     * digabung berbobot antar sumber, diskalakan ke component weight (20).
     */
    private function integritasScore(User $user, KpiPeriod $period, int $componentWeight): float
    {
        $categories = KpiIntegrityCategory::all();

        if ($categories->sum('deduction_value') === 0) {
            return 0.0;
        }

        $validatedReports = ViolationReport::where('reported_user_id', $user->id)
            ->where('period_id', $period->id)
            ->where('category', 'INTEGRITAS')
            ->where('status', 'VALIDATED')
            ->whereNotNull('integrity_category_id')
            ->get();

        $decisions = KpiIntegrityEvaluation::where('reported_user_id', $user->id)
            ->where('period_id', $period->id)
            ->where('decision', 'KURANGIN')
            ->get();

        $sourceScores = [
            'ADUAN_PERUSAHAAN' => $this->integrityCategoryScore($categories, fn (KpiIntegrityCategory $c) => $validatedReports
                ->where('integrity_category_id', $c->id)->isNotEmpty()),
            'PENILAI_1' => $this->integrityCategoryScore($categories, fn (KpiIntegrityCategory $c) => $decisions
                ->where('kpi_integrity_category_id', $c->id)->where('evaluator_role', 'PENILAI_1')->isNotEmpty()),
            'PENILAI_2' => $this->integrityCategoryScore($categories, fn (KpiIntegrityCategory $c) => $decisions
                ->where('kpi_integrity_category_id', $c->id)->where('evaluator_role', 'PENILAI_2')->isNotEmpty()),
            'PENILAI_3' => $this->integrityCategoryScore($categories, fn (KpiIntegrityCategory $c) => $decisions
                ->where('kpi_integrity_category_id', $c->id)->where('evaluator_role', 'PENILAI_3')->isNotEmpty()),
        ];

        $sourceWeights = KpiIntegritySourceWeight::pluck('weight', 'source');
        $weighted = collect($sourceScores)->map(fn (float $score, string $source) => $score * ($sourceWeights[$source] ?? 0) / 100)->sum();

        return round(min($weighted, 100) / 100 * $componentWeight, 2);
    }

    /** Skor 1 sumber Integritas (0-100): kategori nol total kalau $hasIncident($category) true, sisanya penuh. */
    private function integrityCategoryScore(\Illuminate\Support\Collection $categories, \Closure $hasIncident): float
    {
        $max = $categories->sum('deduction_value');
        $remaining = $categories->sum(fn (KpiIntegrityCategory $c) => $hasIncident($c) ? 0 : $c->deduction_value);

        return $max === 0 ? 0.0 : $remaining / $max * 100;
    }

    private function workingDaysIn(KpiPeriod $period): int
    {
        return count($this->workingDaysDates($period));
    }

    /** Hari kerja Senin-Sabtu dalam 1 bulan periode (Minggu diloncat, §8.1.1 AttendanceSeeder). */
    private function workingDaysDates(KpiPeriod $period): array
    {
        $start = \Illuminate\Support\Carbon::create($period->year, $period->month, 1)->startOfMonth();

        return AttendanceService::workingDaysBetween($start, $start->copy()->endOfMonth())->all();
    }
}

<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\KpiComponentWeight;
use App\Models\KpiEvaluatorWeight;
use App\Models\KpiFinalScore;
use App\Models\KpiIntegrityCategory;
use App\Models\KpiPeriod;
use App\Models\KpiPlan;
use App\Models\User;
use App\Models\ViolationReport;
use Carbon\CarbonPeriod;

/**
 * Menghitung kpi_final_scores dari 5 bucket (plan.md §7) saat periode ditutup
 * (Fase C). Dipanggil dari KpiPeriodsTable::updateStatus() saat status ->
 * CLOSED.
 *
 * ponytail: dijalankan sinkron per periode (job kecil, ~ratusan pegawai) —
 * pindah ke queued job kalau jumlah pegawai membesar jauh dari skala saat ini.
 */
class KpiEvaluationService
{
    /** Level jabatan yang masuk cakupan KPI bulanan — Direksi di luar cakupan (§5.2). */
    private const EVALUATED_JOB_LEVELS = [2, 3, 4];

    /** Deduksi flat per aduan Pakaian Dinas tervalidasi kalau `deduction_point` belum diisi manual (§7 poin 4, asumsi — belum ada nilai baku dari user). */
    private const DEFAULT_PAKAIAN_DEDUCTION = 20;

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

        return KpiFinalScore::updateOrCreate(
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
     * §7.1: 8 sub-kategori, tiap kategori mulai dari skor penuh
     * (`deduction_value`-nya sendiri, §10 catatan skema), dikurangi flat per
     * kejadian tervalidasi (dedup harian per kategori), floor 0.
     */
    private function integritasScore(User $user, KpiPeriod $period, int $componentWeight): float
    {
        $categories = KpiIntegrityCategory::all();
        $categoryMaxTotal = $categories->sum('deduction_value');

        if ($categoryMaxTotal === 0) {
            return 0.0;
        }

        $validatedReports = ViolationReport::where('reported_user_id', $user->id)
            ->where('period_id', $period->id)
            ->where('category', 'INTEGRITAS')
            ->where('status', 'VALIDATED')
            ->whereNotNull('integrity_category_id')
            ->get();

        $remainingTotal = $categories->sum(function (KpiIntegrityCategory $category) use ($validatedReports) {
            $incidentDays = $validatedReports
                ->where('integrity_category_id', $category->id)
                ->unique(fn (ViolationReport $report) => $report->incident_date->toDateString())
                ->count();

            return max($category->deduction_value - $incidentDays * $category->deduction_value, 0);
        });

        return round($remainingTotal / $categoryMaxTotal * $componentWeight, 2);
    }

    private function workingDaysIn(KpiPeriod $period): int
    {
        return count($this->workingDaysDates($period));
    }

    /** Hari kerja Senin-Sabtu dalam 1 bulan periode (Minggu diloncat, §8.1.1 AttendanceSeeder). */
    private function workingDaysDates(KpiPeriod $period): array
    {
        $start = \Illuminate\Support\Carbon::create($period->year, $period->month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        return collect(CarbonPeriod::create($start, $end))
            ->filter(fn ($date) => ! $date->isSunday())
            ->all();
    }
}

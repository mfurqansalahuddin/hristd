<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\PaginatesRows;
use App\Livewire\Concerns\SortsColumns;
use App\Models\KpiCycleSchedule;
use App\Models\KpiFinalScore;
use App\Models\KpiPeriod;
use App\Models\KpiPlan;
use App\Models\User;
use App\Services\KpiEvaluationService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class KpiPeriodsTable extends Component
{
    use PaginatesRows, SortsColumns, WithPagination;

    public int $month;

    public int $year;

    public array $statusEdits = [];

    /** Layar konfirmasi bobot ditampilkan sebelum periode benar-benar dibuat (dikonfirmasi user). */
    public bool $confirmingCreate = false;

    #[Url(history: true)]
    public string $status = '';

    public function mount(): void
    {
        $this->month = now()->month;
        $this->year = now()->year;
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['status']);
        $this->resetPage();
    }

    public function openCreateConfirm(): void
    {
        $this->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'min:2020'],
        ]);

        $this->confirmingCreate = true;
    }

    public function cancelCreate(): void
    {
        $this->confirmingCreate = false;
    }

    public function store(): void
    {
        $data = $this->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'min:2020'],
        ]);

        KpiPeriod::create($data);

        $this->confirmingCreate = false;
        session()->flash('success', 'Periode KPI berhasil dibuka.');
    }

    public function updateStatus(int $periodId, KpiEvaluationService $kpiEvaluationService): void
    {
        $status = $this->statusEdits[$periodId] ?? null;

        $this->validate([
            'statusEdits.'.$periodId => ['required', 'string', 'in:DRAFT,WORKING,EVALUATION,DISPUTE,CLOSED'],
        ]);

        $period = KpiPeriod::findOrFail($periodId);
        $wasDraft = $period->status === 'DRAFT';
        $period->update(['status' => $status]);

        // Rencana kerja SUBMITTED yang belum sempat di-approve atasan saat fase DRAFT ditutup
        // dianggap tetap OK (auto-approve on timeout, dikonfirmasi user) — bukan dianggap 0.
        if ($wasDraft && $status !== 'DRAFT') {
            KpiPlan::where('period_id', $period->id)->where('status', 'SUBMITTED')->update(['status' => 'APPROVED']);
        }

        // Fase C (plan.md §12): transisi ke CLOSED memicu hitung kpi_final_scores dari 5 bucket (§7).
        if ($status === 'CLOSED') {
            $kpiEvaluationService->calculateForPeriod($period);
        }

        session()->flash('success', 'Status periode KPI diperbarui.');
    }

    protected function sortableColumns(): array
    {
        return ['year'];
    }

    protected function defaultSort(): string
    {
        return 'year';
    }

    public function render()
    {
        $kpiPeriods = KpiPeriod::when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->orderBy('year', $this->direction)
            ->orderBy('month', $this->direction)
            ->paginate($this->perPage());

        foreach ($kpiPeriods as $period) {
            $this->statusEdits[$period->id] ??= $period->status;
        }

        $periods = $kpiPeriods->getCollection();
        $eligibleUserIds = User::whereIn('job_level', [2, 3, 4])->pluck('id');

        return view('livewire.admin.kpi-periods-table', [
            'kpiPeriods' => $kpiPeriods,
            'perPageOptions' => self::PER_PAGE_OPTIONS,
            'draftStats' => $this->draftPeriodStats($periods, $eligibleUserIds),
            'evaluationStats' => $this->evaluationPeriodStats($periods, $eligibleUserIds),
            'closedStats' => $this->closedPeriodStats($periods),
            'scheduleWarning' => $this->scheduleWarning(),
            'weightsPreview' => $this->confirmingCreate ? KpiPeriod::buildWeightsSnapshot() : null,
        ]);
    }

    /**
     * Banner read-only: bandingkan tanggal hari ini vs fase yang seharusnya menurut master
     * jadwal (`kpi_cycle_schedule`) vs status periode berjalan yang sebenarnya — supaya HRD
     * tidak lupa pindah fase manual (dikonfirmasi user: tetap manual, bukan otomatis).
     */
    private function scheduleWarning(): ?string
    {
        $expectedPhase = KpiCycleSchedule::phaseForDay(now()->day);
        $current = KpiPeriod::current();

        if (! $expectedPhase || ! $current) {
            return null;
        }

        $expectedStatus = match ($expectedPhase->phase) {
            'PENGINGAT_DIBUKA' => 'WORKING',
            'PENILAIAN' => 'EVALUATION',
            'REVIEW_VALIDASI' => 'DISPUTE',
            'FINALISASI' => 'CLOSED',
            default => null,
        };

        if ($expectedStatus === null || $current->status === $expectedStatus) {
            return null;
        }

        return "Hari ini seharusnya sudah masuk fase \"{$expectedPhase->label()}\" (status {$expectedStatus}), tapi periode {$current->month}/{$current->year} masih berstatus {$current->status}. Jangan lupa pindahkan fase manual.";
    }

    /**
     * Bucket fase DRAFT per pegawai eligible (job_level 2-4): belum mengisi rencana,
     * menunggu approval (SUBMITTED), atau sudah di-approve (semua rencananya APPROVED).
     *
     * @param  Collection<int, KpiPeriod>  $periods
     * @param  Collection<int, int>  $eligibleUserIds
     * @return array<int, array{belum: int, menunggu: int, approved: int}>
     */
    private function draftPeriodStats(Collection $periods, Collection $eligibleUserIds): array
    {
        $periodIds = $periods->where('status', 'DRAFT')->pluck('id');

        if ($periodIds->isEmpty()) {
            return [];
        }

        $rows = KpiPlan::whereIn('period_id', $periodIds)
            ->whereIn('user_id', $eligibleUserIds)
            ->selectRaw("period_id, user_id,
                SUM(CASE WHEN status = 'SUBMITTED' THEN 1 ELSE 0 END) as submitted_count,
                SUM(CASE WHEN status = 'APPROVED' THEN 1 ELSE 0 END) as approved_count,
                COUNT(*) as total_count")
            ->groupBy('period_id', 'user_id')
            ->get();

        return $periodIds->mapWithKeys(function (int $periodId) use ($rows, $eligibleUserIds) {
            $userRows = $rows->where('period_id', $periodId);
            $menunggu = $userRows->where('submitted_count', '>', 0)->count();
            $approved = $userRows->filter(fn ($row) => (int) $row->submitted_count === 0 && (int) $row->approved_count === (int) $row->total_count && (int) $row->total_count > 0)->count();

            return [$periodId => [
                'belum' => max(0, $eligibleUserIds->count() - $menunggu - $approved),
                'menunggu' => $menunggu,
                'approved' => $approved,
            ]];
        })->all();
    }

    /**
     * Bucket fase EVALUATION/DISPUTE: sudah dinilai lengkap oleh 3 evaluator vs belum.
     * Dispute & "final" sengaja tidak dihitung — KpiDispute belum punya endpoint sama sekali.
     *
     * @param  Collection<int, KpiPeriod>  $periods
     * @param  Collection<int, int>  $eligibleUserIds
     * @return array<int, array{lengkap: int, belum: int}>
     */
    private function evaluationPeriodStats(Collection $periods, Collection $eligibleUserIds): array
    {
        $periodIds = $periods->whereIn('status', ['EVALUATION', 'DISPUTE'])->pluck('id');

        if ($periodIds->isEmpty()) {
            return [];
        }

        $plans = KpiPlan::whereIn('period_id', $periodIds)
            ->whereIn('user_id', $eligibleUserIds)
            ->where('status', 'APPROVED')
            ->withCount('evaluations')
            ->get(['id', 'user_id', 'period_id']);

        return $periodIds->mapWithKeys(function (int $periodId) use ($plans, $eligibleUserIds) {
            $lengkap = $plans->where('period_id', $periodId)
                ->groupBy('user_id')
                ->filter(fn (Collection $userPlans) => $userPlans->every(fn (KpiPlan $plan) => $plan->evaluations_count >= 3))
                ->count();

            return [$periodId => ['lengkap' => $lengkap, 'belum' => max(0, $eligibleUserIds->count() - $lengkap)]];
        })->all();
    }

    /**
     * Bucket fase CLOSED: jumlah kpi_final_scores per kategori predikat.
     *
     * @param  Collection<int, KpiPeriod>  $periods
     * @return array<int, array<string, int>>
     */
    private function closedPeriodStats(Collection $periods): array
    {
        $periodIds = $periods->where('status', 'CLOSED')->pluck('id');

        if ($periodIds->isEmpty()) {
            return [];
        }

        $finalScores = KpiFinalScore::whereIn('period_id', $periodIds)->get(['period_id', 'grand_total_score']);
        $emptyBuckets = array_fill_keys(array_keys(KpiFinalScore::PREDIKAT_COLORS), 0);

        return $periodIds->mapWithKeys(function (int $periodId) use ($finalScores, $emptyBuckets) {
            $counts = $finalScores->where('period_id', $periodId)->countBy(fn (KpiFinalScore $score) => $score->predikat());

            return [$periodId => array_merge($emptyBuckets, $counts->all())];
        })->all();
    }
}

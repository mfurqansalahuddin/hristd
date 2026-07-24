<?php

namespace App\Livewire\Admin;

use App\Models\KpiPeriod;
use App\Models\KpiPeriodPhase;
use Livewire\Component;

class KpiPeriodPhasesPanel extends Component
{
    public ?int $periodId = null;

    public array $phaseEdits = [];

    /** Snapshot terakhir tersimpan, dipakai bandingkan dirty-state per fase (server-side, bukan Alpine). */
    public array $savedPhaseEdits = [];

    public function mount(): void
    {
        $this->periodId = KpiPeriod::current()?->id;
        $this->syncEdits();
    }

    public function updatedPeriodId(): void
    {
        $this->syncEdits();
    }

    public function isPhaseDirty(string $phase): bool
    {
        $current = $this->phaseEdits[$phase] ?? null;
        $saved = $this->savedPhaseEdits[$phase] ?? null;

        if ($current === null || $saved === null) {
            return $current !== $saved;
        }

        return $current['start_date'] !== $saved['start_date']
            || $current['end_date'] !== $saved['end_date'];
    }

    public function updatePhase(string $phase): void
    {
        $data = $this->validate([
            "phaseEdits.{$phase}.start_date" => ['nullable', 'date'],
            "phaseEdits.{$phase}.end_date" => ['nullable', 'date', 'after_or_equal:phaseEdits.'.$phase.'.start_date'],
        ]);

        KpiPeriodPhase::where('kpi_period_id', $this->periodId)
            ->where('phase', $phase)
            ->update($data['phaseEdits'][$phase]);

        $this->savedPhaseEdits[$phase] = $this->phaseEdits[$phase];

        session()->flash('success', 'Tanggal fase diperbarui.');
    }

    private function syncEdits(): void
    {
        $rows = KpiPeriodPhase::where('kpi_period_id', $this->periodId)->get()->keyBy('phase');

        $this->phaseEdits = collect(KpiPeriodPhase::PHASES)
            ->mapWithKeys(fn (string $phase) => [
                $phase => [
                    'start_date' => optional($rows->get($phase))->start_date?->toDateString(),
                    'end_date' => optional($rows->get($phase))->end_date?->toDateString(),
                ],
            ])
            ->toArray();

        $this->savedPhaseEdits = $this->phaseEdits;
    }

    public function render()
    {
        return view('livewire.admin.kpi-period-phases-panel', [
            'periods' => KpiPeriod::orderByDesc('year')->orderByDesc('month')->get(),
            'labels' => KpiPeriodPhase::LABELS,
        ]);
    }
}

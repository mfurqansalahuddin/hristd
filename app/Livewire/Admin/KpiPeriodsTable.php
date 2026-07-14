<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\PaginatesRows;
use App\Livewire\Concerns\SortsColumns;
use App\Models\KpiPeriod;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class KpiPeriodsTable extends Component
{
    use PaginatesRows, SortsColumns, WithPagination;

    public int $month;

    public int $year;

    public array $statusEdits = [];

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

    public function store(): void
    {
        $data = $this->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'min:2020'],
        ]);

        KpiPeriod::create($data);

        session()->flash('success', 'Periode KPI berhasil dibuka.');
    }

    public function updateStatus(int $periodId): void
    {
        $status = $this->statusEdits[$periodId] ?? null;

        $this->validate([
            'statusEdits.'.$periodId => ['required', 'string', 'in:DRAFT,EVALUATION,DISPUTE,CLOSED'],
        ]);

        KpiPeriod::whereKey($periodId)->update(['status' => $status]);

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

        return view('livewire.admin.kpi-periods-table', [
            'kpiPeriods' => $kpiPeriods,
            'perPageOptions' => self::PER_PAGE_OPTIONS,
        ]);
    }
}

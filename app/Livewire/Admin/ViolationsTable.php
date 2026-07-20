<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\PaginatesRows;
use App\Models\ViolationReport;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Panel HR untuk memvalidasi aduan Pakaian Dinas & Integritas (company-wide,
 * §7.1) — PENDING tidak pernah tervalidasi sebelum layar ini ada. Aduan
 * dikelompokkan per (orang, hari, kategori) — kejadian berulang di hari yang
 * sama untuk orang yang sama dianggap 1 aduan (pola dedup yang sama dipakai
 * pakaianScore()/integritasScore()).
 */
class ViolationsTable extends Component
{
    use PaginatesRows, WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $dateFrom = '';

    #[Url(history: true)]
    public string $dateTo = '';

    /** Konfirmasi dua-langkah in-app (bukan wire:confirm/dialog native — bisa disenyapkan browser tanpa error, lihat CLAUDE.md hristdmobile). */
    public ?string $confirmingGroupKey = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function openValidate(string $groupKey): void
    {
        $this->confirmingGroupKey = $groupKey;
    }

    public function cancelValidate(): void
    {
        $this->confirmingGroupKey = null;
    }

    /** @param array<int, int> $reportIds */
    public function confirmValidate(array $reportIds): void
    {
        ViolationReport::whereIn('id', $reportIds)->where('status', 'PENDING')->update(['status' => 'VALIDATED']);

        $this->confirmingGroupKey = null;
        session()->flash('success', 'Aduan divalidasi.');
    }

    public function render()
    {
        // ponytail: grouping di PHP (bukan SQL GROUP BY) — lebih simpel, aman untuk skala ratusan
        // pegawai. Revisit dengan agregasi di DB kalau backlog PENDING membengkak ribuan baris.
        $groups = ViolationReport::where('status', 'PENDING')
            ->with(['reportedUser:id,name,nik', 'integrityCategory:id,name'])
            ->when($this->search !== '', fn ($query) => $query->whereHas(
                'reportedUser',
                fn ($query) => $query->where('name', 'like', "%{$this->search}%")->orWhere('nik', 'like', "%{$this->search}%")
            ))
            ->when($this->dateFrom !== '', fn ($query) => $query->whereDate('incident_date', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn ($query) => $query->whereDate('incident_date', '<=', $this->dateTo))
            ->orderByDesc('incident_date')
            ->get()
            ->groupBy(fn (ViolationReport $report) => implode('|', [
                $report->reported_user_id, $report->incident_date->toDateString(), $report->category, $report->integrity_category_id,
            ]))
            ->map(fn ($reports, $groupKey) => [
                'group_key' => $groupKey,
                'reported_user' => $reports->first()->reportedUser,
                'category' => $reports->first()->category,
                'integrity_category' => $reports->first()->integrityCategory,
                'description' => $reports->first(fn (ViolationReport $r) => filled($r->description))?->description,
                'photo_path' => $reports->first(fn (ViolationReport $r) => filled($r->photo_path))?->photo_path,
                'incident_date' => $reports->first()->incident_date,
                'count' => $reports->count(),
                'report_ids' => $reports->pluck('id')->all(),
            ])
            ->values();

        $page = $this->getPage();
        $perPage = $this->perPage();

        $paginated = new LengthAwarePaginator(
            $groups->forPage($page, $perPage)->values(),
            $groups->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'pageName' => 'page']
        );

        return view('livewire.admin.violations-table', [
            'groups' => $paginated,
            'perPageOptions' => self::PER_PAGE_OPTIONS,
        ]);
    }
}

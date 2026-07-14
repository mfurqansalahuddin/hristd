<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\PaginatesRows;
use App\Livewire\Concerns\SortsColumns;
use App\Models\LeaveRequest;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class SakitTable extends Component
{
    use PaginatesRows, SortsColumns, WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $status = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'status']);
        $this->resetPage();
    }

    protected function sortableColumns(): array
    {
        return ['start_date', 'status'];
    }

    protected function defaultSort(): string
    {
        return 'start_date';
    }

    public function render()
    {
        $sakitList = LeaveRequest::where('type', LeaveRequest::TYPE_SAKIT)
            ->with(['user', 'approver'])
            ->when($this->search !== '', fn ($query) => $query->whereHas('user', fn ($query) => $query->where('name', 'like', "%{$this->search}%")))
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->orderByRaw("status = 'PENDING' desc")
            ->orderBy($this->sort, $this->direction)
            ->paginate($this->perPage());

        return view('livewire.admin.sakit-table', [
            'sakitList' => $sakitList,
            'perPageOptions' => self::PER_PAGE_OPTIONS,
        ]);
    }
}

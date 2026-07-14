<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\PaginatesRows;
use App\Livewire\Concerns\SortsColumns;
use App\Models\Location;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class LocationsTable extends Component
{
    use PaginatesRows, SortsColumns, WithPagination;

    #[Url(history: true)]
    public string $type = '';

    public function updatedType(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['type']);
        $this->resetPage();
    }

    public function delete(Location $location): void
    {
        $location->delete();

        session()->flash('success', 'Lokasi kantor berhasil dihapus.');
    }

    protected function sortableColumns(): array
    {
        return ['name', 'type'];
    }

    protected function defaultSort(): string
    {
        return 'name';
    }

    public function render()
    {
        return view('livewire.admin.locations-table', [
            'locations' => Location::when($this->type !== '', fn ($query) => $query->where('type', $this->type))
                ->orderBy($this->sort, $this->direction)
                ->paginate($this->perPage()),
            'perPageOptions' => self::PER_PAGE_OPTIONS,
        ]);
    }
}

<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\PaginatesRows;
use App\Livewire\Concerns\SortsColumns;
use App\Models\Department;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class JabatanTable extends Component
{
    use PaginatesRows, SortsColumns, WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $jobLevel = '';

    #[Url(history: true)]
    public string $status = '';

    /** @var array<int, int|string> department_id => user_id dipilih di dropdown "Tetapkan" */
    public array $selected = [];

    public bool $showCreateForm = false;

    public string $newName = '';

    public string $newType = '';

    public string $newParentId = '';

    public string $newUserId = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedJobLevel(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'jobLevel', 'status']);
        $this->resetPage();
    }

    public function openCreateForm(): void
    {
        $this->reset(['newName', 'newType', 'newParentId', 'newUserId']);
        $this->showCreateForm = true;
    }

    public function cancelCreateForm(): void
    {
        $this->showCreateForm = false;
    }

    public function updatedNewType(): void
    {
        $this->newParentId = '';
    }

    public function createJabatan(): void
    {
        $data = $this->validate([
            'newName' => ['required', 'string', 'max:255'],
            'newType' => ['required', 'string', 'in:'.implode(',', Department::CREATABLE_TYPES)],
            'newParentId' => ['required', 'exists:departments,id'],
            'newUserId' => ['required', 'exists:users,id'],
        ]);

        $department = Department::create([
            'name' => $data['newName'],
            'type' => $data['newType'],
            'parent_department_id' => $data['newParentId'],
        ]);

        User::findOrFail($data['newUserId'])->update([
            'department_id' => $department->id,
            'job_level' => $department->headJobLevel(),
        ]);

        $this->showCreateForm = false;
        session()->flash('success', 'Jabatan baru berhasil dibuat dan ditetapkan.');
    }

    /** Induk yang valid buat tipe departemen baru yang lagi dipilih (§3.3 plan.md). */
    public function parentOptions(): Collection
    {
        $parentTypes = Department::PARENT_TYPES[$this->newType] ?? [];

        if ($parentTypes === []) {
            return collect();
        }

        return Department::query()
            ->whereIn('type', $parentTypes)
            ->when($parentTypes === ['DIREKSI'], fn ($query) => $query->whereNotNull('parent_department_id'))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function assign(int $departmentId): void
    {
        $userId = $this->selected[$departmentId] ?? null;

        if (! $userId) {
            return;
        }

        if ($userId === '__none__') {
            $this->vacate($departmentId);
            unset($this->selected[$departmentId]);

            return;
        }

        $department = Department::findOrFail($departmentId);

        User::findOrFail($userId)->update([
            'department_id' => $department->id,
            'job_level' => $department->headJobLevel(),
        ]);

        unset($this->selected[$departmentId]);

        session()->flash('success', 'Jabatan berhasil ditetapkan.');
    }

    public function vacate(int $departmentId): void
    {
        User::where('department_id', $departmentId)
            ->whereIn('job_level', [1, 2, 3])
            ->update(['job_level' => 4]);

        session()->flash('success', 'Jabatan dikosongkan, pemegang lama dikembalikan menjadi Staf.');
    }

    protected function sortableColumns(): array
    {
        return ['department_name', 'level', 'holder_name'];
    }

    protected function defaultSort(): string
    {
        return 'level';
    }

    public function render()
    {
        $positions = Department::query()
            ->when($this->search !== '', fn ($query) => $query->where('name', 'like', "%{$this->search}%"))
            ->with('parent')
            ->get()
            ->map(function (Department $department) {
                $holder = User::where('department_id', $department->id)
                    ->whereIn('job_level', [1, 2, 3])
                    ->first(['id', 'name', 'nik']);

                return [
                    'department' => $department,
                    'department_name' => $department->name,
                    'level' => $department->headJobLevel(),
                    'holder' => $holder,
                    'holder_name' => $holder?->name ?? '',
                ];
            })
            ->when($this->jobLevel !== '', fn ($collection) => $collection->where('level', (int) $this->jobLevel))
            ->when($this->status === 'occupied', fn ($collection) => $collection->whereNotNull('holder'))
            ->when($this->status === 'vacant', fn ($collection) => $collection->whereNull('holder'))
            ->sortBy(fn ($item) => [$item[$this->sort ?: 'level'], $item['department_name']])
            ->values();

        if ($this->direction === 'desc') {
            $positions = $positions->reverse()->values();
        }

        $perPage = $this->perPage();
        $page = $this->getPage();
        $paginated = new LengthAwarePaginator(
            $positions->slice(($page - 1) * $perPage, $perPage)->values(),
            $positions->count(),
            $perPage,
            $page,
        );

        return view('livewire.admin.jabatan-table', [
            'positions' => $paginated,
            'staff' => User::orderBy('name')->get(['id', 'name', 'nik']),
            'parentOptions' => $this->showCreateForm ? $this->parentOptions() : collect(),
            'perPageOptions' => self::PER_PAGE_OPTIONS,
        ]);
    }
}

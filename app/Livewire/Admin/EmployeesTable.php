<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\PaginatesRows;
use App\Livewire\Concerns\SortsColumns;
use App\Models\Department;
use App\Models\User;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class EmployeesTable extends Component
{
    use PaginatesRows, SortsColumns, WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $jobLevel = '';

    #[Url(history: true)]
    public string $departmentId = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedJobLevel(): void
    {
        $this->resetPage();
    }

    public function updatedDepartmentId(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'jobLevel', 'departmentId']);
        $this->resetPage();
    }

    public function delete(User $employee): void
    {
        $employee->delete();

        session()->flash('success', 'Pegawai berhasil dihapus.');
    }

    protected function sortableColumns(): array
    {
        return ['nik', 'name', 'job_level', 'employment_status'];
    }

    protected function defaultSort(): string
    {
        return 'name';
    }

    public function render()
    {
        $employees = User::with('department')
            ->when($this->search !== '', function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', "%{$this->search}%")
                        ->orWhere('nik', 'like', "%{$this->search}%")
                        ->orWhere('username', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->jobLevel !== '', function ($query) {
                if (is_numeric($this->jobLevel)) {
                    $query->where('job_level', (int) $this->jobLevel);
                } else {
                    $query->where('job_level', 2)
                        ->whereHas('department', fn ($query) => $query->where('type', $this->jobLevel));
                }
            })
            ->when($this->departmentId !== '', fn ($query) => $query->where('department_id', $this->departmentId))
            ->orderBy($this->sort, $this->direction)
            ->paginate($this->perPage());

        return view('livewire.admin.employees-table', [
            'employees' => $employees,
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'perPageOptions' => self::PER_PAGE_OPTIONS,
        ]);
    }
}

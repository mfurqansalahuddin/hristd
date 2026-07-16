<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\PaginatesRows;
use App\Livewire\Concerns\SortsColumns;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class DinasLuarTable extends Component
{
    use PaginatesRows, SortsColumns, WithFileUploads, WithPagination;

    #[Url(history: true)]
    public string $search = '';

    public bool $showCreateForm = false;

    /** @var array<int, int> */
    public array $newUserIds = [];

    public string $newStartDate = '';

    public string $newEndDate = '';

    public string $newReason = '';

    public $newAttachment;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search']);
        $this->resetPage();
    }

    public function openCreateForm(): void
    {
        $this->reset(['newUserIds', 'newStartDate', 'newEndDate', 'newReason', 'newAttachment']);
        $this->showCreateForm = true;
    }

    public function cancelCreateForm(): void
    {
        $this->showCreateForm = false;
    }

    public function createDinasLuar(AttendanceService $attendanceService): void
    {
        $data = $this->validate([
            'newUserIds' => ['required', 'array', 'min:1'],
            'newUserIds.*' => ['exists:users,id'],
            'newStartDate' => ['required', 'date'],
            'newEndDate' => ['required', 'date', 'after_or_equal:newStartDate'],
            'newReason' => ['required', 'string', 'max:1000'],
            'newAttachment' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        $attachmentPath = $this->newAttachment->store('leave-requests', 'public');
        $batchUuid = (string) Str::ulid();

        foreach ($data['newUserIds'] as $userId) {
            $leaveRequest = LeaveRequest::create([
                'user_id' => $userId,
                'type' => LeaveRequest::TYPE_DINAS_LUAR,
                'start_date' => $data['newStartDate'],
                'end_date' => $data['newEndDate'],
                'reason' => $data['newReason'],
                'attachment_path' => $attachmentPath,
                'status' => 'APPROVED',
                'source' => LeaveRequest::SOURCE_HR_MANUAL,
                'approved_by_id' => auth()->id(),
                'dl_batch_uuid' => $batchUuid,
            ]);

            $attendanceService->injectAttendanceForApprovedLeave($leaveRequest);
        }

        $this->showCreateForm = false;
        session()->flash('success', count($data['newUserIds']).' pegawai berhasil dicatat dinas luar.');
    }

    protected function sortableColumns(): array
    {
        return ['start_date'];
    }

    protected function defaultSort(): string
    {
        return 'start_date';
    }

    public function render()
    {
        $dinasLuarList = LeaveRequest::where('type', LeaveRequest::TYPE_DINAS_LUAR)
            ->with('user')
            ->when($this->search !== '', fn ($query) => $query->whereHas('user', fn ($query) => $query->where('name', 'like', "%{$this->search}%")))
            ->orderBy($this->sort, $this->direction)
            ->paginate($this->perPage());

        return view('livewire.admin.dinas-luar-table', [
            'dinasLuarList' => $dinasLuarList,
            'employees' => User::orderBy('name')->get(['id', 'name', 'nik']),
            'perPageOptions' => self::PER_PAGE_OPTIONS,
        ]);
    }
}

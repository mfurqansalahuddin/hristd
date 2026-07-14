<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\PaginatesRows;
use App\Livewire\Concerns\SortsColumns;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\AttendanceService;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class CutiTable extends Component
{
    use PaginatesRows, SortsColumns, WithFileUploads, WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $status = '';

    public bool $showCreateForm = false;

    public string $newUserId = '';

    public string $newStartDate = '';

    public string $newEndDate = '';

    public string $newReason = '';

    public string $newStatus = 'APPROVED';

    public $newAttachment;

    public ?int $approvingId = null;

    public $approveAttachment;

    public ?int $rejectingId = null;

    public string $rejectReason = '';

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

    public function openCreateForm(): void
    {
        $this->reset(['newUserId', 'newStartDate', 'newEndDate', 'newReason', 'newAttachment']);
        $this->newStatus = 'APPROVED';
        $this->showCreateForm = true;
    }

    public function cancelCreateForm(): void
    {
        $this->showCreateForm = false;
    }

    public function createCuti(AttendanceService $attendanceService): void
    {
        $data = $this->validate([
            'newUserId' => ['required', 'exists:users,id'],
            'newStartDate' => ['required', 'date'],
            'newEndDate' => ['required', 'date', 'after_or_equal:newStartDate'],
            'newReason' => ['required', 'string', 'max:1000'],
            'newStatus' => ['required', 'in:PENDING,APPROVED,REJECTED'],
            'newAttachment' => ['nullable', 'file', 'max:5120'],
        ]);

        $leaveRequest = LeaveRequest::create([
            'user_id' => $data['newUserId'],
            'type' => LeaveRequest::TYPE_CUTI,
            'start_date' => $data['newStartDate'],
            'end_date' => $data['newEndDate'],
            'reason' => $data['newReason'],
            'status' => $data['newStatus'],
            'source' => LeaveRequest::SOURCE_HR_MANUAL,
            'attachment_path' => $this->newAttachment?->store('leave-requests', 'public'),
            'approved_by_id' => $data['newStatus'] !== 'PENDING' ? auth()->id() : null,
        ]);

        if ($leaveRequest->status === 'APPROVED') {
            $attendanceService->injectAttendanceForApprovedLeave($leaveRequest);
        }

        $this->showCreateForm = false;
        session()->flash('success', 'Cuti berhasil dicatat.');
    }

    public function openApprove(int $leaveRequestId): void
    {
        $this->rejectingId = null;
        $this->approvingId = $leaveRequestId;
        $this->approveAttachment = null;
    }

    public function cancelApprove(): void
    {
        $this->approvingId = null;
    }

    public function confirmApprove(AttendanceService $attendanceService): void
    {
        $this->validate([
            'approveAttachment' => ['required', 'file', 'max:5120'],
        ]);

        $leaveRequest = LeaveRequest::findOrFail($this->approvingId);
        $leaveRequest->update([
            'status' => 'APPROVED',
            'attachment_path' => $this->approveAttachment->store('leave-requests', 'public'),
            'approved_by_id' => auth()->id(),
        ]);

        $attendanceService->injectAttendanceForApprovedLeave($leaveRequest);

        $this->approvingId = null;
        $this->approveAttachment = null;
        session()->flash('success', 'Cuti disetujui, kehadiran otomatis tercatat.');
    }

    public function openReject(int $leaveRequestId): void
    {
        $this->approvingId = null;
        $this->rejectingId = $leaveRequestId;
        $this->rejectReason = '';
    }

    public function cancelReject(): void
    {
        $this->rejectingId = null;
    }

    public function confirmReject(): void
    {
        $data = $this->validate([
            'rejectReason' => ['required', 'string', 'max:1000'],
        ]);

        LeaveRequest::whereKey($this->rejectingId)->update([
            'status' => 'REJECTED',
            'rejection_reason' => $data['rejectReason'],
            'approved_by_id' => auth()->id(),
        ]);

        $this->rejectingId = null;
        session()->flash('success', 'Cuti ditolak.');
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
        $cutiList = LeaveRequest::where('type', LeaveRequest::TYPE_CUTI)
            ->with(['user', 'approver'])
            ->when($this->search !== '', fn ($query) => $query->whereHas('user', fn ($query) => $query->where('name', 'like', "%{$this->search}%")))
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->orderByRaw("status = 'PENDING' desc")
            ->orderBy($this->sort, $this->direction)
            ->paginate($this->perPage());

        return view('livewire.admin.cuti-table', [
            'cutiList' => $cutiList,
            'employees' => User::orderBy('name')->get(['id', 'name', 'nik']),
            'perPageOptions' => self::PER_PAGE_OPTIONS,
        ]);
    }
}

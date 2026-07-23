<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\PaginatesRows;
use App\Livewire\Concerns\SortsColumns;
use App\Mail\AccountDeletionProcessed;
use App\Models\AccountDeletionRequest;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class DeletionRequestsTable extends Component
{
    use PaginatesRows, SortsColumns, WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $status = '';

    public ?int $approvingId = null;

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

    public function openApprove(int $deletionRequestId): void
    {
        $this->rejectingId = null;
        $this->approvingId = $deletionRequestId;
    }

    public function cancelApprove(): void
    {
        $this->approvingId = null;
    }

    public function confirmApprove(): void
    {
        $deletionRequest = AccountDeletionRequest::findOrFail($this->approvingId);

        $user = $deletionRequest->user;
        if ($user) {
            $user->tokens()->delete();
            $user->delete();
        }

        $deletionRequest->update([
            'status' => AccountDeletionRequest::STATUS_APPROVED,
            'reviewed_by_id' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        Mail::to($deletionRequest->email)->queue(new AccountDeletionProcessed($deletionRequest));

        $this->approvingId = null;
        session()->flash('success', 'Akun berhasil dihapus.');
    }

    public function openReject(int $deletionRequestId): void
    {
        $this->approvingId = null;
        $this->rejectingId = $deletionRequestId;
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

        $deletionRequest = AccountDeletionRequest::findOrFail($this->rejectingId);
        $deletionRequest->update([
            'status' => AccountDeletionRequest::STATUS_REJECTED,
            'rejection_reason' => $data['rejectReason'],
            'reviewed_by_id' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        Mail::to($deletionRequest->email)->queue(new AccountDeletionProcessed($deletionRequest));

        $this->rejectingId = null;
        session()->flash('success', 'Permintaan ditolak.');
    }

    protected function sortableColumns(): array
    {
        return ['created_at', 'status'];
    }

    protected function defaultSort(): string
    {
        return 'created_at';
    }

    public function render()
    {
        $deletionRequests = AccountDeletionRequest::with(['user', 'reviewer'])
            ->when($this->search !== '', fn ($query) => $query->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%"))
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->orderByRaw("status = 'PENDING' desc")
            ->orderBy($this->sort, $this->direction)
            ->paginate($this->perPage());

        return view('livewire.admin.deletion-requests-table', [
            'deletionRequests' => $deletionRequests,
            'perPageOptions' => self::PER_PAGE_OPTIONS,
        ]);
    }
}

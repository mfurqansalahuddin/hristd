@if ($participant->approval_status === 'PENDING')
    <div class="flex items-center gap-2">
        <button type="button" wire:click="approve({{ $participant->id }})"
            class="rounded-lg bg-success-50 px-2.5 py-1.5 text-xs font-medium text-success-600 hover:bg-success-100 dark:bg-success-500/15 dark:text-success-500">Approve</button>
        <button type="button" wire:click="reject({{ $participant->id }})"
            class="rounded-lg bg-error-50 px-2.5 py-1.5 text-xs font-medium text-error-600 hover:bg-error-100 dark:bg-error-500/15 dark:text-error-500">Tolak</button>
    </div>
@elseif ($participant->approval_status === 'APPROVED')
    <x-ui.badge color="success">Disetujui</x-ui.badge>
@elseif ($participant->approval_status === 'REJECTED')
    <x-ui.badge color="error">Ditolak</x-ui.badge>
@endif

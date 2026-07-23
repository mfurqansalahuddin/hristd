@php $status = $this->edits[$participant->id]['status'] ?? null; @endphp
<div wire:key="participant-mobile-{{ $participant->id }}" class="flex flex-col gap-3 p-4">
    <div>
        <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $participant->user?->name }}</p>
        <p class="text-gray-400 text-theme-xs">
            NIK {{ $participant->user?->nik }}
            @if ($showJabatan ?? false)
                &middot; {{ $participant->user?->jabatanLabel() }}
                @if ($participant->user?->department)
                    &middot; {{ $participant->user->department->name }}
                @endif
            @endif
        </p>
    </div>

    @include('livewire.admin.partials.mandatory-event-status-chips', ['participant' => $participant])

    @if (in_array($status, ['TELAT', 'TIDAK_HADIR'], true))
        @include('livewire.admin.partials.mandatory-event-reason-field', ['participant' => $participant])
    @endif

    @if ($participant->approval_status)
        <div class="border-t border-gray-100 pt-3 dark:border-gray-800">
            <p class="mb-1.5 text-theme-xs text-gray-400">Approval HR</p>
            @include('livewire.admin.partials.mandatory-event-approval-block', ['participant' => $participant])
        </div>
    @endif

    @include('livewire.admin.partials.mandatory-event-save-button', ['participant' => $participant, 'block' => true])
</div>

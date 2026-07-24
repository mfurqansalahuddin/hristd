@php $status = $this->edits[$participant->id]['status'] ?? null; @endphp
<tr wire:key="participant-{{ $participant->id }}" class="border-b border-gray-100 dark:border-gray-800">
    <td class="px-5 py-4 sm:px-6">
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
    </td>
    <td class="px-5 py-4 sm:px-6">
        @include('livewire.admin.partials.mandatory-event-status-chips', ['participant' => $participant])
    </td>
    <td class="px-5 py-4 sm:px-6">
        @if (in_array($status, ['TELAT', 'TIDAK_HADIR'], true))
            @include('livewire.admin.partials.mandatory-event-reason-field', ['participant' => $participant])
        @else
            <p class="text-gray-400 text-theme-sm">-</p>
        @endif
    </td>
    <td class="px-5 py-4 sm:px-6">
        @if ($participant->approval_status)
            @include('livewire.admin.partials.mandatory-event-approval-block', ['participant' => $participant])
        @else
            <p class="text-gray-400 text-theme-sm">-</p>
        @endif
    </td>
</tr>

@php
    $status = $this->edits[$participant->id]['status'] ?? null;
    $editing = $this->isReasonEditing($participant);
@endphp
@if ($editing)
    <div class="mb-1 flex items-center justify-between gap-2">
        <span class="text-theme-xs text-gray-400">Alasan</span>
        @if (filled($this->edits[$participant->id]['reason'] ?? null))
            <button type="button" wire:click="save({{ $participant->id }})"
                class="inline-flex items-center gap-1 text-theme-xs font-medium text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-400">
                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M5.5 9V7a4.5 4.5 0 0 1 9 0v2M5 9h10a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-6a1 1 0 0 1 1-1Z" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                Kunci
            </button>
        @endif
    </div>
    <textarea wire:model="edits.{{ $participant->id }}.reason" rows="2"
        placeholder="{{ $status === 'TIDAK_HADIR' ? 'Wajib diisi, mis. Sakit / Dinas Luar / Piket Malam' : 'Opsional, catatan keterlambatan' }}"
        class="dark:bg-dark-900 shadow-theme-xs w-full min-w-[200px] rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90"></textarea>
    @error("edits.{$participant->id}.reason") <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
@else
    <div class="flex items-start justify-between gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 dark:border-gray-800 dark:bg-white/5">
        <p class="text-sm text-gray-600 dark:text-gray-300">{{ $participant->reason }}</p>
        <button type="button" wire:click="editReason({{ $participant->id }})"
            class="shrink-0 text-theme-xs font-medium text-brand-500 hover:underline">Ubah</button>
    </div>
@endif

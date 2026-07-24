<div class="flex flex-wrap gap-2">
    @foreach ([
        'HADIR' => ['Hadir', 'peer-checked:border-success-500/40 peer-checked:bg-success-50 peer-checked:text-success-600 dark:peer-checked:border-success-500/30 dark:peer-checked:bg-success-500/15 dark:peer-checked:text-success-500'],
        'TELAT' => ['Telat', 'peer-checked:border-warning-400/40 peer-checked:bg-warning-50 peer-checked:text-warning-600 dark:peer-checked:border-warning-400/30 dark:peer-checked:bg-warning-500/15 dark:peer-checked:text-orange-400'],
        'TIDAK_HADIR' => ['Tidak Hadir', 'peer-checked:border-error-500/40 peer-checked:bg-error-50 peer-checked:text-error-600 dark:peer-checked:border-error-500/30 dark:peer-checked:bg-error-500/15 dark:peer-checked:text-error-500'],
    ] as $value => [$label, $checkedClass])
        <label class="{{ $this->isLocked() ? 'cursor-not-allowed opacity-60' : 'cursor-pointer' }}">
            <input type="radio" wire:model.live="edits.{{ $participant->id }}.status" value="{{ $value }}" class="peer sr-only" @disabled($this->isLocked()) />
            <span class="inline-flex h-9 items-center rounded-lg border border-gray-300 px-3 text-xs font-medium text-gray-500 dark:border-gray-700 dark:text-gray-400 md:h-8 {{ $checkedClass }}">
                {{ $label }}
            </span>
        </label>
    @endforeach
</div>
@error("edits.{$participant->id}.status") <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror

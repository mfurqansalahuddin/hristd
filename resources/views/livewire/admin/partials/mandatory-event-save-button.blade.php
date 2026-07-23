@php $dirty = $this->isDirty($participant->id); @endphp
@if ($block ?? false)
    <button type="button" wire:click="save({{ $participant->id }})" @disabled(! $dirty)
        class="h-10 w-full rounded-lg text-sm font-medium {{ $dirty ? 'bg-brand-500 text-white hover:bg-brand-600' : 'bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-600' }}">
        Simpan
    </button>
@else
    <button type="button" wire:click="save({{ $participant->id }})" @disabled(! $dirty)
        class="text-sm {{ $dirty ? 'text-brand-500 hover:underline' : 'text-gray-300 dark:text-gray-600 cursor-not-allowed' }}">
        Simpan
    </button>
@endif

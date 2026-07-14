@props(['options'])

<div class="w-full sm:w-auto">
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tampilkan</label>
    <select wire:model.live="perPage"
        class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 sm:w-24 dark:border-gray-700 dark:text-white/90">
        @foreach ($options as $option)
            <option value="{{ $option }}">{{ $option }}</option>
        @endforeach
    </select>
</div>

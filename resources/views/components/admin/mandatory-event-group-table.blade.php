@props(['label', 'people', 'showJabatan' => false, 'summary' => null])

<div class="mb-6 overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]" x-data="{ open: true }">
    <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-5 py-3 dark:border-gray-800">
        <button type="button" @click="open = !open" class="flex flex-1 items-center justify-between text-left">
            <div>
                <p class="text-sm font-semibold text-gray-800 dark:text-white/90">{{ $label }}</p>
                <p class="mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-theme-xs text-gray-400">
                    <span>{{ $people->count() }} orang</span>
                    @if ($summary)
                        <span class="text-success-600 dark:text-success-500">{{ $summary['hadir'] }} Hadir</span>
                        <span class="text-warning-600 dark:text-orange-400">{{ $summary['telat'] }} Telat</span>
                        <span class="text-error-600 dark:text-error-500">{{ $summary['tidak_hadir'] }} Tidak Hadir</span>
                    @endif
                </p>
            </div>
            <svg class="h-4 w-4 shrink-0 text-gray-400 transition-transform" :class="open ? '' : '-rotate-90'"
                viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M6 8L10 12L14 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>

        @php $dirty = ! $this->isLocked() && $this->isGroupDirty($people->pluck('id')->all()); @endphp
        <button type="button" wire:click="saveGroup([{{ $people->pluck('id')->implode(',') }}])" @disabled(! $dirty)
            class="h-9 shrink-0 rounded-lg px-4 text-sm font-medium {{ $dirty ? 'bg-brand-500 text-white hover:bg-brand-600' : 'bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-600' }}">
            Simpan
        </button>
    </div>

    <div x-show="open">
        {{-- Desktop (md+): full table --}}
        <div class="hidden max-w-full overflow-x-auto custom-scrollbar md:block">
            <table class="w-full min-w-[900px]">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Nama</p></th>
                        <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Status</p></th>
                        <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Alasan</p></th>
                        <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Approval HR</p></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($people as $participant)
                        @include('livewire.admin.partials.mandatory-event-participant-row-desktop', ['participant' => $participant, 'showJabatan' => $showJabatan])
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile (below md): stacked cards, one field per line instead of a horizontally-scrolled table --}}
        <div class="divide-y divide-gray-100 md:hidden dark:divide-gray-800">
            @foreach ($people as $participant)
                @include('livewire.admin.partials.mandatory-event-participant-row-mobile', ['participant' => $participant, 'showJabatan' => $showJabatan])
            @endforeach
        </div>
    </div>
</div>

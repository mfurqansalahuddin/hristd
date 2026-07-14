@props([
    'field' => null,
    'label',
    'sort' => null,
    'direction' => 'asc',
    'active' => false,
])

@php
    $isSorted = $field && $sort === $field;
    $highlighted = $isSorted || $active;
    $hasPopover = $field || ! $slot->isEmpty();
@endphp

<th {{ $attributes->merge(['class' => 'px-5 py-3 text-left sm:px-6 '.($highlighted ? 'bg-brand-50 dark:bg-brand-500/10' : '')]) }}>
    @if (! $hasPopover)
        <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">{{ $label }}</p>
    @else
        <div x-data="{
                isOpen: false,
                popperInstance: null,
                init() {
                    this.$nextTick(() => {
                        this.popperInstance = createPopper(this.$refs.button, this.$refs.content, {
                            placement: 'bottom-start',
                            strategy: 'fixed',
                            modifiers: [{ name: 'offset', options: { offset: [0, 4] } }],
                        });
                    });
                },
                toggle() {
                    this.isOpen = !this.isOpen;
                    if (this.popperInstance) { this.popperInstance.update(); }
                }
            }"
            @click.away="isOpen = false">
            <button type="button" x-ref="button" @click="toggle()"
                class="inline-flex items-center gap-1 font-medium text-theme-xs {{ $highlighted ? 'text-brand-600 dark:text-brand-400' : 'text-gray-500 dark:text-gray-400' }}">
                <span>{{ $label }}</span>
                @if ($isSorted)
                    <span>{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                @endif
                @if ($field)
                    {{-- sort affordance: up/down arrows, shown whenever this column can be sorted --}}
                    <svg class="h-3 w-3" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 8L10 4L14 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M6 12L10 16L14 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                @endif
                @if (! $slot->isEmpty())
                    {{-- filter affordance: funnel icon, shown whenever this column has filter options --}}
                    <svg class="h-3 w-3" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 4.5H17L12 10.5V15.5L8 17V10.5L3 4.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                @endif
            </button>

            <div class="z-50 fixed" x-ref="content">
                <div x-show="isOpen" x-cloak class="w-56 space-y-3 rounded-2xl border border-gray-200 bg-white p-3 shadow-lg dark:border-gray-800 dark:bg-gray-dark">
                    @if ($field)
                        <div class="flex gap-2">
                            <button type="button" wire:click="sortBy('{{ $field }}', 'asc')" @click="isOpen = false"
                                class="flex-1 rounded-lg border px-2 py-1.5 text-xs hover:bg-gray-50 dark:hover:bg-white/5 {{ $isSorted && $direction === 'asc' ? 'border-brand-500 text-brand-600 dark:text-brand-400' : 'border-gray-200 text-gray-600 dark:border-gray-700 dark:text-gray-300' }}">
                                A&ndash;Z &uarr;
                            </button>
                            <button type="button" wire:click="sortBy('{{ $field }}', 'desc')" @click="isOpen = false"
                                class="flex-1 rounded-lg border px-2 py-1.5 text-xs hover:bg-gray-50 dark:hover:bg-white/5 {{ $isSorted && $direction === 'desc' ? 'border-brand-500 text-brand-600 dark:text-brand-400' : 'border-gray-200 text-gray-600 dark:border-gray-700 dark:text-gray-300' }}">
                                Z&ndash;A &darr;
                            </button>
                        </div>
                    @endif

                    @if (! $slot->isEmpty())
                        <div class="space-y-2 {{ $field ? 'border-t border-gray-100 pt-2 dark:border-gray-800' : '' }}">
                            {{ $slot }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</th>

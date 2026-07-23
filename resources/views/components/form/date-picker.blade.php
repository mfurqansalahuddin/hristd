@props([
    'id' => 'datepicker-' . uniqid(),
    'mode' => 'single', // 'single', 'multiple', 'range', 'time'
    'defaultDate' => null,
    'label' => null,
    'placeholder' => 'Select date',
    'name' => null,
    'dateFormat' => 'Y-m-d',
    'allowInput' => false, // biarkan user ketik manual, bukan cuma klik dari kalender/jam
    'enableTime' => false,
    'noCalendar' => false, // dipakai bareng enableTime buat jadi time-picker murni (jam saja, tanpa kalender)
    'time24hr' => true,
])

@php
    $isTimeOnly = $noCalendar && $enableTime;
@endphp

<div x-data="{
    flatpickrInstance: null,
    init() {
        this.$nextTick(() => {
            this.flatpickrInstance = flatpickr(this.$refs.dateInput, {
                mode: '{{ $mode }}',
                // ponytail: appendTo document.body (flatpickr default when `static` is omitted) so the
                // calendar escapes any ancestor's overflow-hidden/overflow-x-auto (table cards, scroll
                // wrappers) instead of getting clipped by them; flatpickr auto-flips above the input
                // when there isn't room below, computed against the viewport, not the ancestor box.
                disableMobile: true,
                monthSelectorType: 'static',
                dateFormat: '{{ $dateFormat }}',
                allowInput: {{ $allowInput ? 'true' : 'false' }},
                enableTime: {{ $enableTime ? 'true' : 'false' }},
                noCalendar: {{ $noCalendar ? 'true' : 'false' }},
                time_24hr: {{ $time24hr ? 'true' : 'false' }},
                defaultDate: {{ $defaultDate ? (is_array($defaultDate) ? json_encode($defaultDate) : "'" . $defaultDate . "'") : 'null' }},
                onChange: (selectedDates, dateStr, instance) => {
                    this.$dispatch('date-change', {
                        selectedDates,
                        dateStr,
                        instance
                    });
                }
            });
        });
    },
    destroy() {
        if (this.flatpickrInstance) {
            this.flatpickrInstance.destroy();
            this.flatpickrInstance = null;
        }
    }
}" x-init="init()" x-destroy="destroy()"
    x-on:datepicker-set-date.window="if ($event.detail.id === '{{ $id }}' && flatpickrInstance) { $event.detail.date ? flatpickrInstance.setDate($event.detail.date) : flatpickrInstance.clear(); }">
    @if($label)
        <label for="{{ $id }}" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            {{ $label }}
        </label>
    @endif

    <div class="relative custom-datepicker">
        <input
            x-ref="dateInput"
            type="text"
            id="{{ $id }}"
            name="{{ $name }}"
            placeholder="{{ $placeholder }}"
            class="h-11 w-full rounded-lg border appearance-none px-4 py-2.5 text-sm shadow-theme-xs placeholder:text-gray-400 focus:outline-hidden focus:ring-3 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 bg-transparent text-gray-800 border-gray-300 focus:border-brand-300 focus:ring-brand-500/20 dark:border-gray-700 dark:focus:border-brand-800"
            autocomplete="off"
        />
        <span class="absolute text-gray-500 -translate-y-1/2 pointer-events-none right-3 top-1/2 dark:text-gray-400">
            @if ($isTimeOnly)
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="none" class="size-6">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12 3.25C7.16751 3.25 3.25 7.16751 3.25 12C3.25 16.8325 7.16751 20.75 12 20.75C16.8325 20.75 20.75 16.8325 20.75 12C20.75 7.16751 16.8325 3.25 12 3.25ZM1.75 12C1.75 6.33908 6.33908 1.75 12 1.75C17.6609 1.75 22.25 6.33908 22.25 12C22.25 17.6609 17.6609 22.25 12 22.25C6.33908 22.25 1.75 17.6609 1.75 12ZM12 6.25C12.4142 6.25 12.75 6.58579 12.75 7V11.6893L15.5303 14.4697C15.8232 14.7626 15.8232 15.2374 15.5303 15.5303C15.2374 15.8232 14.7626 15.8232 14.4697 15.5303L11.4697 12.5303C11.329 12.3897 11.25 12.1989 11.25 12V7C11.25 6.58579 11.5858 6.25 12 6.25Z" fill="currentColor"></path>
                </svg>
            @else
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="none" class="size-6">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M8 2C8.41421 2 8.75 2.33579 8.75 2.75V3.75H15.25V2.75C15.25 2.33579 15.5858 2 16 2C16.4142 2 16.75 2.33579 16.75 2.75V3.75H18.5C19.7426 3.75 20.75 4.75736 20.75 6V9V19C20.75 20.2426 19.7426 21.25 18.5 21.25H5.5C4.25736 21.25 3.25 20.2426 3.25 19V9V6C3.25 4.75736 4.25736 3.75 5.5 3.75H7.25V2.75C7.25 2.33579 7.58579 2 8 2ZM8 5.25H5.5C5.08579 5.25 4.75 5.58579 4.75 6V8.25H19.25V6C19.25 5.58579 18.9142 5.25 18.5 5.25H16H8ZM19.25 9.75H4.75V19C4.75 19.4142 5.08579 19.75 5.5 19.75H18.5C18.9142 19.75 19.25 19.4142 19.25 19V9.75Z" fill="currentColor"></path>
                </svg>
            @endif
        </span>
    </div>
</div>

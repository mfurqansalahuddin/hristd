@php
    $options = [
        ['id' => 'optionOne', 'label' => 'Monthly'],
        ['id' => 'optionTwo', 'label' => 'Quarterly'],
        ['id' => 'optionThree', 'label' => 'Annually'],
    ];
@endphp

<div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/3">
    <div class="mb-8 flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                Users & Revenue Statistics
            </h3>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">
                Visualize month-to-month progress and engagement.
            </p>
        </div>

        <div x-data="{ selected: 'optionOne' }"
            class="inline-flex h-11 w-fit items-center gap-0.5 rounded-lg bg-gray-100 p-0.5 dark:bg-gray-900">
            @foreach ($options as $option)
                <button @click="selected = '{{ $option['id'] }}'"
                    :class="selected === '{{ $option['id'] }}' ? 'shadow-theme-xs text-gray-900 dark:text-white bg-white dark:bg-gray-800' :
                        'text-gray-500 dark:text-gray-400'"
                    class="h-10 rounded-md px-3 py-2.5 text-theme-sm font-medium transition-colors hover:text-gray-900 dark:hover:bg-gray-800 dark:hover:text-white">
                    {{ $option['label'] }}
                </button>
            @endforeach
        </div>
    </div>

    <div class="custom-scrollbar max-w-full overflow-x-auto">
        <div id="chartTwentyThree" class="apexcharts-tooltip-active -ml-4 min-w-[1000px] pl-2 xl:min-w-full"></div>
    </div>
</div>

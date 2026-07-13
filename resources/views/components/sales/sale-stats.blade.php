@php
    $salesStats = [
        [
            'label' => 'Total Revenue',
            'value' => '$10,590',
            'trend' => '32%',
            'is_up' => true,
            'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15.484 7.72335C15.484 6.28004 14.314 5.11 12.8707 5.11H11.8138C9.99228 5.11 8.51562 6.58666 8.51562 8.4082C8.51562 9.783 9.36841 11.0136 10.6557 11.4964L13.344 12.5046C14.6312 12.9873 15.484 14.2179 15.484 15.5927C15.484 17.4143 14.0074 18.8909 12.1858 18.8909H11.129C9.68566 18.8909 8.51562 17.7209 8.51562 16.2776M11.9996 19.2831L11.9996 21.2085M11.9996 2.79199L11.9996 4.71734" stroke="#12B76A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'chart_id' => 'chartTwentyFive-01',
        ],
        [
            'label' => 'Total Sales',
            'value' => '1,320',
            'trend' => '32%',
            'is_up' => true,
            'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.25 8L2 8M3.5 12H2M2.75 16H2M7.91619 19.1243H19.1489C19.9166 19.1243 20.5603 18.5448 20.6407 17.7814L21.8249 6.53203C21.9181 5.64637 21.2237 4.875 20.3331 4.875H9.10039C8.33275 4.875 7.68899 5.45455 7.60863 6.21796L6.42443 17.4673C6.3312 18.3529 7.02564 19.1243 7.91619 19.1243ZM13.5391 4.875H16.2108L15.4608 9.86401H12.7891L13.5391 4.875Z" stroke="#7A5AF8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'chart_id' => 'chartTwentyFive-02',
        ],
        [
            'label' => 'Avg. Order Value', // Renamed for better UX, originally was "Total Sales" twice
            'value' => '4.38%',
            'trend' => '32%',
            'is_up' => true,
            'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18.752 7.37695H5.25196M15.3773 4.00098L18.75 7.37587L15.3773 10.751M5.25 16.625H18.75M8.62471 20.001L5.25196 16.6261L8.62471 13.251" stroke="#0BA5EC" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'chart_id' => 'chartTwentyFive-03',
        ],
        [
            'label' => 'Refund Rate',
            'value' => '1.2%',
            'trend' => '32%',
            'is_up' => false, // Set to false to show "Vs last month" correctly if that's preferred, but user had green up arrow in HTML. I'll stick to their HTML logic.
            'is_up' => true, // Match user's HTML trend color/icon
            'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20.6389 10.8655V8.16168C20.6389 7.3664 19.9942 6.72168 19.1989 6.72168H4.31891C3.52361 6.72168 2.87891 7.3664 2.87891 8.16168V16.3217C2.87891 17.117 3.52361 17.7617 4.31891 17.7617H8.96575M12.7199 10.2372C12.429 10.0976 12.1032 10.0193 11.759 10.0193C10.5315 10.0193 9.53656 11.0144 9.53656 12.2417C9.53656 12.587 9.61532 12.914 9.75587 13.2056M11.7589 15.1717H18.1689C19.5331 15.1717 20.6389 16.2776 20.6389 17.6417C20.6389 19.0058 19.5331 20.1116 18.1689 20.1116H14.2357M11.7589 15.1717L13.6777 13.2517M11.7589 15.1717L13.6777 17.0917" stroke="#F04438" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> <path d="M6.20312 12.2422H6.21312" stroke="#F04438" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'chart_id' => 'chartTwentyFive-04',
        ],
    ];
@endphp

<div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3">
    <div class="mb-5 flex flex-col justify-between gap-5 sm:flex-row">
        <div>
            <h2 class="mb-1 text-xl font-semibold text-gray-800 dark:text-white/90">
                Sales Dashboard
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Track revenue, performance, and sales growth in real-time
            </p>
        </div>
        <div class="flex flex-row-reverse justify-center gap-3 lg:flex-row">
            <!-- Date input -->
            <div x-data="{
                init() {
                    flatpickr(this.$refs.datepicker, {
                        mode: 'range',
                        static: true,
                        disableMobile: true,
                        monthSelectorType: 'static',
                        dateFormat: 'M j',
                        defaultDate: [new Date(Date.now() - 6 * 24 * 60 * 60 * 1000), new Date()],
                        prevArrow: '<svg class=\'stroke-current\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\' fill=\'none\' xmlns=\'http://www.w3.org/2000/svg\'><path d=\'M15.25 6L9 12.25L15.25 18.5\' stroke=\'\' stroke-width=\'1.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\'/></svg>',
                        nextArrow: '<svg class=\'stroke-current\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\' fill=\'none\' xmlns=\'http://www.w3.org/2000/svg\'><path d=\'M8.75 19L15 12.75L8.75 6.5\' stroke=\'\' stroke-width=\'1.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\'/></svg>',
                        onReady: (selectedDates, dateStr, instance) => {
                            instance.element.value = dateStr.replace('to', '-');
                            const customClass = instance.element.getAttribute('data-class');
                            if (instance.calendarContainer) {
                                instance.calendarContainer.classList.add(customClass);
                            }
                        },
                        onChange: (selectedDates, dateStr, instance) => {
                            instance.element.value = dateStr.replace('to', '-');
                        },
                    })
                }
            }" class="relative flex h-11 w-fit">
                <input x-ref="datepicker" class="datepicker text-theme-sm shadow-theme-xs h-11 w-full min-w-[100px] rounded-lg border border-gray-300 bg-white py-2.5 pr-4 pl-10 font-medium text-gray-700 focus:ring-0 focus:outline-hidden focus-visible:outline-hidden xl:min-w-[100px] xl:pl-11 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400" placeholder="Select dates" data-class="flatpickr-right" readonly="readonly" />
                <div class="pointer-events-none absolute inset-0 right-auto left-4 flex items-center">
                    <svg class="fill-gray-700 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M6.66683 1.54199C7.08104 1.54199 7.41683 1.87778 7.41683 2.29199V3.00033H12.5835V2.29199C12.5835 1.87778 12.9193 1.54199 13.3335 1.54199C13.7477 1.54199 14.0835 1.87778 14.0835 2.29199V3.00033L15.4168 3.00033C16.5214 3.00033 17.4168 3.89576 17.4168 5.00033V7.50033V15.8337C17.4168 16.9382 16.5214 17.8337 15.4168 17.8337H4.5835C3.47893 17.8337 2.5835 16.9382 2.5835 15.8337V7.50033V5.00033C2.5835 3.89576 3.47893 3.00033 4.5835 3.00033L5.91683 3.00033V2.29199C5.91683 1.87778 6.25262 1.54199 6.66683 1.54199ZM6.66683 4.50033H4.5835C4.30735 4.50033 4.0835 4.72418 4.0835 5.00033V6.75033H15.9168V5.00033C15.9168 4.72418 15.693 4.50033 15.4168 4.50033H13.3335H6.66683ZM15.9168 8.25033H4.0835V15.8337C4.0835 16.1098 4.30735 16.3337 4.5835 16.3337H15.4168C15.693 16.3337 15.9168 16.1098 15.9168 15.8337V8.25033Z" fill="" />
                    </svg>
                </div>
            </div>
            <button
                class="shadow-theme-xs inline-flex h-11 items-center gap-2 rounded-lg bg-white px-4 py-3 text-sm font-medium text-gray-700 ring-1 ring-gray-300 transition ring-inset hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M14.6547 5.90384C14.6547 4.48402 13.5037 3.33301 12.0839 3.33301C10.664 3.33301 9.51304 4.48403 9.51302 5.90384M14.6547 5.90384C14.6547 7.32367 13.5037 8.47467 12.0839 8.47467C10.664 8.47467 9.51302 7.32367 9.51302 5.90384M14.6547 5.90384L17.7096 5.90381M9.51302 5.90384L2.29297 5.90381M5.34792 14.0955C5.34792 12.6757 6.49892 11.5247 7.91875 11.5247C9.33858 11.5247 10.4896 12.6757 10.4896 14.0955M5.34792 14.0955C5.34792 15.5153 6.49892 16.6663 7.91875 16.6663C9.33858 16.6663 10.4896 15.5153 10.4896 14.0955M5.34792 14.0955L2.29297 14.0955M10.4896 14.0955L17.7096 14.0955" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>

                Filter
            </button>
            <button
                class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex h-11 items-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white transition">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M16.6661 13.333V15.4163C16.6661 16.1067 16.1064 16.6663 15.4161 16.6663H4.58203C3.89168 16.6663 3.33203 16.1067 3.33203 15.4163V13.333M10.0004 3.33301L10.0004 13.333M6.14456 7.18684L9.9986 3.33525L13.8529 7.18684" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                Export
            </button>
        </div>
    </div>
    <div class="rounded-2xl bg-gray-100 p-1 dark:bg-white/3">
        <div class="grid grid-cols-1 gap-1 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($salesStats as $stat)
                <div class="rounded-xl bg-white p-5 dark:bg-gray-900">
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-gray-700 dark:text-gray-400">
                                {{ $stat['label'] }}
                            </h3>
                            <div class="mt-1.5 flex gap-1.5">
                                <p
                                    class="{{ $stat['is_up'] ? 'text-success-600' : 'text-error-600' }} flex items-center gap-1 text-sm font-medium">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="{{ $stat['is_up'] ? 'M7.9974 2.66602L7.9974 13.3336M4 6.66334L7.99987 2.66602L12 6.66334' : 'M7.9974 13.3336L7.9974 2.66602M4 9.33666L7.99987 13.3336L12 9.33666' }}" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    {{ $stat['trend'] }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    vs last month
                                </p>
                            </div>
                        </div>
                        <div>
                            {!! $stat['icon'] !!}
                        </div>
                    </div>
                    <div class="flex items-end justify-between">
                        <h2 class="w-1/2 text-3xl font-semibold text-gray-800 dark:text-white/90">
                            {{ $stat['value'] }}
                        </h2>
                        <div class="chartTwentyFive {{ $stat['chart_id'] }} h-11 w-1/2"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

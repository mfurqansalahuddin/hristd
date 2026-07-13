@php
    $years = ['2025', '2024', '2023', '2022', '2021', '2020'];
    $periods = ['3 Month', '6 Month', '1 Year'];
@endphp

<div
  class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/3"
  x-data="{ incomeHidden: false, expenseHidden: false, hoverSeries: null }"
>
  <div class="mb-6 flex flex-col justify-between gap-5 sm:flex-row">
    <div>
      <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
        Cashflow Overview
      </h3>
    </div>
    <div class="flex gap-2">
      <!-- Year Dropdown -->
      <div
        class="relative"
        x-data="{
            openDate: false,
            selectedDate: '{{ $years[0] }}',
            periods: @js($years)
          }"
        @click.outside="openDate = false"
      >
        <button
          @click="openDate = !openDate"
          class="flex h-9 items-center justify-center gap-1.5 rounded-lg border border-gray-300 px-2.5 text-sm font-medium text-gray-700 shadow-xs dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
        >
          <span x-text="selectedDate"></span>
          <svg
            :class="openDate ? 'rotate-180' : ''"
            width="18"
            height="18"
            viewBox="0 0 18 18"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M4.3125 7.21875L9 11.9063L13.6875 7.21875"
              stroke="currentColor"
              stroke-width="1.5"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
        </button>

        <!-- Dropdown -->
        <div
          x-show="openDate"
          class="absolute right-0 z-50 mt-1.5 w-38 rounded-xl border border-gray-200 bg-white p-1.5 shadow-lg dark:border-gray-700 dark:bg-gray-900"
        >
          <template x-for="period in periods" :key="period">
            <button
              @click="selectedDate = period; openDate = false"
              class="w-full rounded-lg px-2.5 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5"
              :class="selectedDate === period ? 'bg-gray-100 dark:bg-white/5 font-medium' : 'font-normal'"
              x-text="period"
            ></button>
          </template>
        </div>
      </div>
      <!-- Date Dropdown -->
      <div
        class="relative"
        x-data="{
            openDate: false,
            selectedDate: '{{ $periods[0] }}',
            periods: @js($periods)
          }"
        @click.outside="openDate = false"
      >
        <button
          @click="openDate = !openDate"
          class="flex h-9 items-center justify-center gap-1.5 rounded-lg border border-gray-300 px-2.5 text-sm font-medium text-gray-700 shadow-xs dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
        >
          <span x-text="selectedDate"></span>
          <svg
            :class="openDate ? 'rotate-180' : ''"
            width="18"
            height="18"
            viewBox="0 0 18 18"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M4.3125 7.21875L9 11.9063L13.6875 7.21875"
              stroke="currentColor"
              stroke-width="1.5"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
        </button>

        <!-- Dropdown -->
        <div
          x-show="openDate"
          class="absolute right-0 z-50 mt-1.5 w-38 rounded-xl border border-gray-200 bg-white p-1.5 shadow-lg dark:border-gray-700 dark:bg-gray-900"
        >
          <template x-for="period in periods" :key="period">
            <button
              @click="selectedDate = period; openDate = false"
              class="w-full rounded-lg px-2.5 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5"
              :class="selectedDate === period ? 'bg-gray-100 dark:bg-white/5 font-medium' : 'font-normal'"
              x-text="period"
            ></button>
          </template>
        </div>
      </div>
    </div>
  </div>
  <div class="flex flex-wrap items-end justify-between gap-5">
    <div>
      <p class="mb-1.5 text-sm text-gray-500 dark:text-gray-400">
        Total Revenue
      </p>
      <div class="flex items-center gap-3">
        <h4 class="text-2xl font-medium text-gray-800 dark:text-white/90">
          $9,758.00
        </h4>
        <span
          class="bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-500 flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium"
        >
          +7.96%
        </span>
      </div>
    </div>

    <div class="flex items-center gap-5">
      <div
        class="flex cursor-pointer items-center gap-2 transition-opacity duration-200 select-none hover:opacity-80"
        :class="incomeHidden ? 'opacity-40' : ''"
        @mouseenter="if(!incomeHidden && !expenseHidden) hoverSeries = 0"
        @mouseleave="hoverSeries = null"
        @click="incomeHidden = !incomeHidden; window.chart29.toggleSeries('Income')"
      >
        <span class="bg-brand-500 block size-2.5 rounded-full"></span>
        <span class="text-sm font-normal text-gray-800 dark:text-white/90"
          >Income</span
        >
      </div>
      <div
        class="flex cursor-pointer items-center gap-2 transition-opacity duration-200 select-none hover:opacity-80"
        :class="expenseHidden ? 'opacity-40' : ''"
        @mouseenter="if(!incomeHidden && !expenseHidden) hoverSeries = 1"
        @mouseleave="hoverSeries = null"
        @click="expenseHidden = !expenseHidden; window.chart29.toggleSeries('Expense')"
      >
        <span class="bg-brand-300 block size-2.5 rounded-full"></span>
        <span class="text-sm font-normal text-gray-800 dark:text-white/90"
          >Expense</span
        >
      </div>
    </div>
  </div>

  <div class="-ml-4 h-[250px]">
    <div
      id="chartTwentyNine"
      :class="hoverSeries !== null ? `highlight-series-${hoverSeries}` : ''"
    ></div>
  </div>
</div>

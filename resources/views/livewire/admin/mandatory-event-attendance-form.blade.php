<div>
    <x-ui.flash-success />

    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">{{ $event->name }}</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ $event->date->format('d-m-Y') }}
            @if ($event->time)
                &middot; {{ \Illuminate\Support\Carbon::parse($event->time)->format('H:i') }}
            @endif
        </p>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs text-gray-500 dark:text-gray-400">Hadir</p>
            <p class="mt-1 text-xl font-semibold text-success-600 dark:text-success-500">{{ $summary['hadir'] }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs text-gray-500 dark:text-gray-400">Telat</p>
            <p class="mt-1 text-xl font-semibold text-warning-600 dark:text-orange-400">{{ $summary['telat'] }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs text-gray-500 dark:text-gray-400">Tidak Hadir</p>
            <p class="mt-1 text-xl font-semibold text-error-600 dark:text-error-500">{{ $summary['tidak_hadir'] }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs text-gray-500 dark:text-gray-400">Belum Mengisi</p>
            <p class="mt-1 text-xl font-semibold text-gray-700 dark:text-white/90">{{ $summary['belum'] }}</p>
        </div>
        @if ($summary['perlu_approval'] > 0)
            <div class="rounded-xl border border-warning-200 bg-warning-50 p-4 dark:border-warning-500/30 dark:bg-warning-500/10">
                <p class="text-theme-xs text-warning-600 dark:text-orange-400">Perlu Approval HR</p>
                <p class="mt-1 text-xl font-semibold text-warning-600 dark:text-orange-400">{{ $summary['perlu_approval'] }}</p>
            </div>
        @endif
    </div>

    <div class="mb-6 flex flex-col gap-3 sm:flex-row">
        <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari nama atau NIK..."
            class="dark:bg-dark-900 shadow-theme-xs h-11 w-full flex-1 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:text-white/90" />
        <select wire:model.live="filterStatus"
            class="dark:bg-dark-900 shadow-theme-xs h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 sm:w-56 dark:border-gray-700 dark:text-white/90">
            <option value="semua">Semua Status</option>
            <option value="hadir">Hadir</option>
            <option value="telat">Telat</option>
            <option value="tidak_hadir">Tidak Hadir</option>
            <option value="belum">Belum Mengisi</option>
        </select>
    </div>

    @if ($noResults)
        <div class="rounded-xl border border-gray-200 bg-white py-12 text-center text-sm text-gray-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400">
            Tidak ada peserta yang cocok dengan pencarian/filter.
        </div>
    @else
        @if ($leaders->isNotEmpty())
            <x-admin.mandatory-event-group-table label="Pejabat" :people="$leaders" :show-jabatan="true" :summary="$leadersSummary" />
        @endif

        @foreach ($staffGroups as $group)
            <div wire:key="staff-group-{{ $loop->index }}">
                <x-admin.mandatory-event-group-table :label="$group['label']" :people="$group['people']" :summary="$group['summary']" />
            </div>
        @endforeach
    @endif
</div>

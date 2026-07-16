<div>
    @if (session('success'))
        <div class="mb-6">
            <x-ui.alert variant="success" :message="session('success')" />
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]" wire:loading.class="opacity-60">
        <div class="flex flex-col gap-3 border-b border-gray-100 p-4 sm:flex-row sm:flex-wrap sm:items-end sm:gap-3 sm:p-6 dark:border-gray-800">
            <x-common.data-table.per-page-select :options="$perPageOptions" />
            <div class="w-full sm:w-56">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Cari</label>
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="Nama atau NIK"
                    class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90" />
            </div>
            <div class="w-full sm:w-44" wire:ignore x-on:date-change="$wire.set('date', $event.detail.dateStr)">
                <x-form.date-picker id="attendance-date" label="Tanggal" :default-date="$date" date-format="Y-m-d" />
            </div>
            @if ($location || $statusMasuk || $statusPulang || $search || $date !== now()->toDateString())
                <button type="button" wire:click="resetFilters" class="text-sm text-gray-500 hover:underline sm:mb-3 dark:text-gray-400">Reset</button>
            @endif
        </div>

        <div class="max-w-full overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[1100px]">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <x-common.data-table.th field="name" label="Nama" :sort="$sort" :direction="$direction" />
                        <x-common.data-table.th field="date" label="Tanggal" :sort="$sort" :direction="$direction" />
                        <x-common.data-table.th field="clock_in" label="Jam Masuk" :sort="$sort" :direction="$direction" />
                        <x-common.data-table.th label="Lokasi Masuk" :active="$location !== ''">
                            <select wire:model.live="location"
                                class="dark:bg-dark-900 shadow-theme-xs w-full rounded-lg border border-gray-300 bg-transparent px-2 py-1.5 text-xs text-gray-800 dark:border-gray-700 dark:text-white/90">
                                <option value="">- Semua Lokasi -</option>
                                @foreach ($locations as $officeLocation)
                                    <option value="{{ $officeLocation->id }}">{{ $officeLocation->name }}</option>
                                @endforeach
                                <option value="OUTSIDE">Luar Lokasi Kantor</option>
                            </select>
                        </x-common.data-table.th>
                        <x-common.data-table.th label="Status Masuk" :active="$statusMasuk !== ''">
                            <select wire:model.live="statusMasuk"
                                class="dark:bg-dark-900 shadow-theme-xs w-full rounded-lg border border-gray-300 bg-transparent px-2 py-1.5 text-xs text-gray-800 dark:border-gray-700 dark:text-white/90">
                                <option value="">- Semua -</option>
                                <option value="TEPAT_WAKTU">Tepat Waktu</option>
                                <option value="TERLAMBAT">Terlambat</option>
                            </select>
                        </x-common.data-table.th>
                        <x-common.data-table.th field="clock_out" label="Jam Pulang" :sort="$sort" :direction="$direction" />
                        <x-common.data-table.th label="Lokasi Pulang" :active="$location !== ''">
                            <select wire:model.live="location"
                                class="dark:bg-dark-900 shadow-theme-xs w-full rounded-lg border border-gray-300 bg-transparent px-2 py-1.5 text-xs text-gray-800 dark:border-gray-700 dark:text-white/90">
                                <option value="">- Semua Lokasi -</option>
                                @foreach ($locations as $officeLocation)
                                    <option value="{{ $officeLocation->id }}">{{ $officeLocation->name }}</option>
                                @endforeach
                                <option value="OUTSIDE">Luar Lokasi Kantor</option>
                            </select>
                        </x-common.data-table.th>
                        <x-common.data-table.th label="Status Pulang" :active="$statusPulang !== ''">
                            <select wire:model.live="statusPulang"
                                class="dark:bg-dark-900 shadow-theme-xs w-full rounded-lg border border-gray-300 bg-transparent px-2 py-1.5 text-xs text-gray-800 dark:border-gray-700 dark:text-white/90">
                                <option value="">- Semua -</option>
                                <option value="TEPAT_WAKTU">Tepat Waktu</option>
                                <option value="CEPAT">Pulang Cepat</option>
                            </select>
                        </x-common.data-table.th>
                        <th class="px-5 py-3 text-left sm:px-6">
                            <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Approval HR</p>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendances as $attendance)
                        @php
                            $clockInLocation = $attendance->matchedLocation('clock_in', $locations);
                            $clockOutLocation = $attendance->matchedLocation('clock_out', $locations);
                            $statusMasuk = $attendance->statusMasuk();
                            $statusPulang = $attendance->statusPulang();
                            $needsApproval = $statusMasuk === 'TERLAMBAT'
                                || $statusPulang === 'CEPAT'
                                || ($attendance->clock_in && ! $clockInLocation)
                                || ($attendance->clock_out && ! $clockOutLocation);
                        @endphp
                        <tr wire:key="attendance-{{ $attendance->id }}" class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-5 py-4 sm:px-6"><p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $attendance->user?->name }}</p></td>
                            <td class="px-5 py-4 sm:px-6"><p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $attendance->date->format('d-m-Y') }}</p></td>
                            <td class="px-5 py-4 sm:px-6"><p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $attendance->clock_in?->format('H:i') ?? '-' }}</p></td>
                            <td class="px-5 py-4 sm:px-6">
                                @if ($attendance->clock_in)
                                    <x-ui.badge color="{{ $clockInLocation ? 'success' : 'error' }}">{{ $clockInLocation?->name ?? 'Luar Lokasi Kantor' }}</x-ui.badge>
                                @else
                                    <p class="text-gray-400 text-theme-sm">-</p>
                                @endif
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                @if ($statusMasuk)
                                    <x-ui.badge color="{{ $statusMasuk === 'TEPAT_WAKTU' ? 'success' : 'warning' }}">{{ $statusMasuk === 'TEPAT_WAKTU' ? 'Tepat Waktu' : 'Terlambat' }}</x-ui.badge>
                                @else
                                    <p class="text-gray-400 text-theme-sm">-</p>
                                @endif
                            </td>
                            <td class="px-5 py-4 sm:px-6"><p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $attendance->clock_out?->format('H:i') ?? '-' }}</p></td>
                            <td class="px-5 py-4 sm:px-6">
                                @if ($attendance->clock_out)
                                    <x-ui.badge color="{{ $clockOutLocation ? 'success' : 'error' }}">{{ $clockOutLocation?->name ?? 'Luar Lokasi Kantor' }}</x-ui.badge>
                                @else
                                    <p class="text-gray-400 text-theme-sm">-</p>
                                @endif
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                @if ($statusPulang)
                                    <x-ui.badge color="{{ $statusPulang === 'TEPAT_WAKTU' ? 'success' : 'warning' }}">{{ $statusPulang === 'TEPAT_WAKTU' ? 'Tepat Waktu' : 'Pulang Cepat' }}</x-ui.badge>
                                @else
                                    <p class="text-gray-400 text-theme-sm">-</p>
                                @endif
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                @if (! $needsApproval)
                                    <p class="text-gray-400 text-theme-sm">-</p>
                                @elseif ($attendance->supervisor_approval === 'PENDING')
                                    <div class="flex items-center gap-2" title="{{ $attendance->approval_reason }}">
                                        <button type="button" wire:click="approve({{ $attendance->id }})"
                                            class="rounded-lg bg-success-50 px-2.5 py-1.5 text-xs font-medium text-success-600 hover:bg-success-100 dark:bg-success-500/15 dark:text-success-500">Approve</button>
                                        <button type="button" wire:click="reject({{ $attendance->id }})"
                                            class="rounded-lg bg-error-50 px-2.5 py-1.5 text-xs font-medium text-error-600 hover:bg-error-100 dark:bg-error-500/15 dark:text-error-500">Tolak</button>
                                    </div>
                                @elseif ($attendance->supervisor_approval === 'APPROVED')
                                    <x-ui.badge color="success">Disetujui</x-ui.badge>
                                @else
                                    <x-ui.badge color="error">Ditolak</x-ui.badge>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada data kehadiran pada tanggal ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
            {{ $attendances->links() }}
        </div>
    </div>
</div>

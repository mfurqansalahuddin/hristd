<div>
    @if (session('success'))
        <div class="mb-6">
            <x-ui.alert variant="success" :message="session('success')" />
        </div>
    @endif

    <div class="mb-6 flex gap-2">
        <button type="button" wire:click="$set('mode', 'periode')"
            class="rounded-lg px-4 py-2 text-sm font-medium {{ $mode === 'periode' ? 'bg-brand-500 text-white' : 'bg-white text-gray-700 ring-1 ring-inset ring-gray-300 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700' }}">
            Per Periode
        </button>
        <button type="button" wire:click="$set('mode', 'pegawai')"
            class="rounded-lg px-4 py-2 text-sm font-medium {{ $mode === 'pegawai' ? 'bg-brand-500 text-white' : 'bg-white text-gray-700 ring-1 ring-inset ring-gray-300 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700' }}">
            Per Pegawai (Riwayat)
        </button>
    </div>

    @if ($mode === 'periode')
        <div class="mb-6 flex flex-wrap items-end gap-4">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Periode</label>
                <select wire:model.live="periodId"
                    class="dark:bg-dark-900 shadow-theme-xs h-11 w-48 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">
                    @foreach ($periods as $period)
                        <option value="{{ $period->id }}">{{ $period->month }}/{{ $period->year }} ({{ $period->status }})</option>
                    @endforeach
                </select>
            </div>
            @if ($periodId)
                <a href="{{ route('admin.kpi-final-scores.export', ['period_id' => $periodId]) }}"
                    class="inline-flex items-center justify-center rounded-lg bg-white px-5 py-3.5 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700">
                    Export CSV
                </a>
            @endif
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]" wire:loading.class="opacity-60">
            <div class="flex flex-col gap-3 border-b border-gray-100 p-4 sm:flex-row sm:flex-wrap sm:items-end sm:p-6 dark:border-gray-800">
                <x-common.data-table.per-page-select :options="$perPageOptions" />
            </div>

            <div class="max-w-full overflow-x-auto custom-scrollbar">
                <table class="w-full min-w-[900px]">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <x-common.data-table.th label="Nama" />
                            <x-common.data-table.th label="Jabatan" />
                            <x-common.data-table.th label="Kinerja" />
                            <x-common.data-table.th label="Kehadiran" />
                            <x-common.data-table.th label="Apel" />
                            <x-common.data-table.th label="Pakaian Dinas" />
                            <x-common.data-table.th label="Integritas" />
                            <x-common.data-table.th label="Grand Total" />
                            <x-common.data-table.th label="Persentase Gaji" />
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($scores as $score)
                            <tr wire:key="final-score-{{ $score->id }}" class="border-b border-gray-100 dark:border-gray-800">
                                <td class="px-5 py-4 sm:px-6">
                                    <p class="text-gray-800 text-theme-sm dark:text-white/90">{{ $score->user->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $score->user->nik }}</p>
                                </td>
                                <td class="px-5 py-4 text-theme-sm text-gray-600 sm:px-6 dark:text-gray-400">{{ $score->user->jabatanLabel() }}</td>
                                <td class="px-5 py-4 text-theme-sm text-gray-600 sm:px-6 dark:text-gray-400">{{ $score->score_kinerja }}</td>
                                <td class="px-5 py-4 text-theme-sm text-gray-600 sm:px-6 dark:text-gray-400">{{ $score->score_kehadiran }}</td>
                                <td class="px-5 py-4 text-theme-sm text-gray-600 sm:px-6 dark:text-gray-400">{{ $score->score_apel }}</td>
                                <td class="px-5 py-4 text-theme-sm text-gray-600 sm:px-6 dark:text-gray-400">{{ $score->score_pakaian }}</td>
                                <td class="px-5 py-4 text-theme-sm text-gray-600 sm:px-6 dark:text-gray-400">{{ $score->score_integritas }}</td>
                                <td class="px-5 py-4 text-theme-sm font-medium text-gray-800 sm:px-6 dark:text-white/90">{{ $score->grand_total_score }}</td>
                                <td class="px-5 py-4 sm:px-6">
                                    <x-ui.badge color="{{ $score->predikatColor() }}">{{ $score->salary_percentage }}%</x-ui.badge>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-6 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada nilai akhir untuk periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
                {{ $scores->links() }}
            </div>
        </div>
    @else
        <div class="mb-6">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Cari Nama/NIK Pegawai</label>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Ketik nama atau NIK..."
                class="dark:bg-dark-900 shadow-theme-xs h-11 w-full max-w-md rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">

            @if ($matchingUsers->isNotEmpty())
                <div class="mt-2 max-w-md overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800">
                    @foreach ($matchingUsers as $user)
                        <button type="button" wire:click="selectUser({{ $user->id }})"
                            class="block w-full border-b border-gray-100 px-4 py-2 text-left text-sm last:border-b-0 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-white/5 {{ $selectedUserId === $user->id ? 'bg-brand-50 dark:bg-brand-500/10' : '' }}">
                            {{ $user->name }} <span class="text-xs text-gray-400">({{ $user->nik }})</span>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        @if ($selectedUser)
            <h3 class="mb-3 text-lg font-medium text-gray-800 dark:text-white/90">Riwayat Skor: {{ $selectedUser->name }}</h3>

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]" wire:loading.class="opacity-60">
                <div class="max-w-full overflow-x-auto custom-scrollbar">
                    <table class="w-full min-w-[700px]">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <x-common.data-table.th label="Periode" />
                                <x-common.data-table.th label="Kinerja" />
                                <x-common.data-table.th label="Kehadiran" />
                                <x-common.data-table.th label="Apel" />
                                <x-common.data-table.th label="Pakaian Dinas" />
                                <x-common.data-table.th label="Integritas" />
                                <x-common.data-table.th label="Grand Total" />
                                <x-common.data-table.th label="Persentase Gaji" />
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($scores as $score)
                                <tr wire:key="history-{{ $score->id }}" class="border-b border-gray-100 dark:border-gray-800">
                                    <td class="px-5 py-4 text-theme-sm text-gray-800 sm:px-6 dark:text-white/90">{{ $score->period->month }}/{{ $score->period->year }}</td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-600 sm:px-6 dark:text-gray-400">{{ $score->score_kinerja }}</td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-600 sm:px-6 dark:text-gray-400">{{ $score->score_kehadiran }}</td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-600 sm:px-6 dark:text-gray-400">{{ $score->score_apel }}</td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-600 sm:px-6 dark:text-gray-400">{{ $score->score_pakaian }}</td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-600 sm:px-6 dark:text-gray-400">{{ $score->score_integritas }}</td>
                                    <td class="px-5 py-4 text-theme-sm font-medium text-gray-800 sm:px-6 dark:text-white/90">{{ $score->grand_total_score }}</td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <x-ui.badge color="{{ $score->predikatColor() }}">{{ $score->salary_percentage }}%</x-ui.badge>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-6 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada riwayat nilai akhir untuk pegawai ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
                    {{ $scores->links() }}
                </div>
            </div>
        @endif
    @endif
</div>

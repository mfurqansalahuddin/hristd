<div>
    @if (session('success'))
        <div class="mb-6">
            <x-ui.alert variant="success" :message="session('success')" />
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]" wire:loading.class="opacity-60">
        <div class="flex flex-col gap-3 border-b border-gray-100 p-4 sm:flex-row sm:flex-wrap sm:items-end sm:p-6 dark:border-gray-800">
            <x-common.data-table.per-page-select :options="$perPageOptions" />
            <div class="w-full sm:w-56">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Cari</label>
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="Nama / NIK pegawai"
                    class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90" />
            </div>
            <div class="w-full sm:w-44">
                <div wire:ignore x-on:date-change="$wire.set('dateFrom', $event.detail.dateStr)">
                    <x-form.date-picker id="violation-date-from" label="Dari Tanggal" :default-date="$dateFrom ?: null" date-format="Y-m-d" />
                </div>
            </div>
            <div class="w-full sm:w-44">
                <div wire:ignore x-on:date-change="$wire.set('dateTo', $event.detail.dateStr)">
                    <x-form.date-picker id="violation-date-to" label="Sampai Tanggal" :default-date="$dateTo ?: null" date-format="Y-m-d" />
                </div>
            </div>
            @if ($search || $dateFrom || $dateTo)
                <button type="button" wire:click="resetFilters" class="text-sm text-gray-500 hover:underline sm:mb-3 dark:text-gray-400">Reset filter</button>
            @endif
        </div>

        <div class="max-w-full overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[900px]">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <x-common.data-table.th label="Pegawai" />
                        <x-common.data-table.th label="Tanggal" />
                        <x-common.data-table.th label="Jenis Aduan" />
                        <x-common.data-table.th label="Deskripsi" />
                        <x-common.data-table.th label="Foto" />
                        <x-common.data-table.th label="Jumlah" />
                        <x-common.data-table.th label="Aksi" />
                    </tr>
                </thead>
                <tbody>
                    @forelse ($groups as $group)
                        <tr wire:key="group-{{ $group['group_key'] }}" class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-5 py-4 sm:px-6">
                                <p class="text-gray-800 text-theme-sm dark:text-white/90">{{ $group['reported_user']->name }}</p>
                                <p class="text-gray-400 text-theme-xs">{{ $group['reported_user']->nik }}</p>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $group['incident_date']->format('d M Y') }}</p>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <x-ui.badge :color="$group['category'] === 'PAKAIAN_DINAS' ? 'warning' : 'error'">
                                    {{ $group['category'] === 'PAKAIAN_DINAS' ? 'Pakaian Dinas' : 'Integritas' }}
                                </x-ui.badge>
                                @if ($group['integrity_category'])
                                    <p class="mt-1 text-gray-400 text-theme-xs">{{ $group['integrity_category']->name }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $group['description'] ?? '-' }}</p>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                @if ($group['photo_path'])
                                    <a href="{{ asset('storage/'.$group['photo_path']) }}" target="_blank" class="text-sm text-brand-500 hover:underline">Lihat</a>
                                @else
                                    <span class="text-sm text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $group['count'] }}</p>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                @if ($confirmingGroupKey === $group['group_key'])
                                    <div class="flex items-center gap-3">
                                        <button type="button" wire:click="confirmValidate({{ json_encode($group['report_ids']) }})"
                                            class="text-sm font-semibold text-success-600 hover:underline">Yakin validasi?</button>
                                        <button type="button" wire:click="cancelValidate" class="text-sm text-gray-400 hover:underline">Batal</button>
                                    </div>
                                @else
                                    <button type="button" wire:click="openValidate('{{ $group['group_key'] }}')" class="text-sm text-success-500 hover:underline">
                                        Validasi
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-sm text-gray-400">Tidak ada aduan menunggu validasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
            {{ $groups->links() }}
        </div>
    </div>
</div>

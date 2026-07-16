<div>
    {{-- <div class="mb-4 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400">
        Persetujuan sakit dilakukan atasan pertama pegawai lewat aplikasi mobile (§16.3 plan.md) — halaman ini
        khusus pemantauan HR, tanpa aksi approve/tolak.
    </div> --}}

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]" wire:loading.class="opacity-60">
        <div class="flex flex-col gap-3 border-b border-gray-100 p-4 sm:flex-row sm:flex-wrap sm:items-end sm:p-6 dark:border-gray-800">
            <x-common.data-table.per-page-select :options="$perPageOptions" />
            <div class="w-full sm:w-56">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Cari</label>
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="Nama pegawai"
                    class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90" />
            </div>
            @if ($search || $status)
                <button type="button" wire:click="resetFilters" class="text-sm text-gray-500 hover:underline sm:mb-3 dark:text-gray-400">Reset filter</button>
            @endif
        </div>

        <div class="max-w-full overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[900px]">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <x-common.data-table.th label="Pegawai" />
                        <x-common.data-table.th field="start_date" label="Rentang Tanggal" :sort="$sort" :direction="$direction" />
                        <x-common.data-table.th label="Surat Dokter" />
                        <x-common.data-table.th field="status" label="Status" :sort="$sort" :direction="$direction" :active="$status !== ''">
                            <select wire:model.live="status"
                                class="dark:bg-dark-900 shadow-theme-xs w-full rounded-lg border border-gray-300 bg-transparent px-2 py-1.5 text-xs text-gray-800 dark:border-gray-700 dark:text-white/90">
                                <option value="">- Semua -</option>
                                @foreach (['PENDING', 'APPROVED', 'REJECTED'] as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                        </x-common.data-table.th>
                        <x-common.data-table.th label="Disetujui/Ditolak Oleh" />
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sakitList as $sakit)
                        <tr wire:key="sakit-{{ $sakit->id }}" class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-5 py-4 sm:px-6"><p class="text-gray-800 text-theme-sm dark:text-white/90">{{ $sakit->user->name }}</p></td>
                            <td class="px-5 py-4 sm:px-6">
                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                    {{ $sakit->start_date->format('d M Y') }} - {{ $sakit->end_date->format('d M Y') }}
                                </p>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                @if ($sakit->attachment_path)
                                    <a href="{{ asset('storage/'.$sakit->attachment_path) }}" target="_blank" class="text-sm text-brand-500 hover:underline">Lihat</a>
                                @else
                                    <span class="text-sm text-gray-400">1 hari, tanpa surat</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <x-ui.badge :color="match ($sakit->status) {
                                    'APPROVED' => 'success',
                                    'REJECTED' => 'error',
                                    default => 'warning',
                                }">
                                    {{ $sakit->status }}
                                </x-ui.badge>
                                @if ($sakit->status === 'REJECTED' && $sakit->rejection_reason)
                                    <p class="mt-1 text-gray-400 text-theme-xs">{{ $sakit->rejection_reason }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $sakit->approver->name ?? '-' }}</p>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
            {{ $sakitList->links() }}
        </div>
    </div>
</div>

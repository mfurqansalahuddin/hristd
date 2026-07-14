<div>
    @if (session('success'))
        <div class="mb-6">
            <x-ui.alert variant="success" :message="session('success')" />
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]" wire:loading.class="opacity-60">
        <div class="flex flex-col gap-3 border-b border-gray-100 p-4 sm:flex-row sm:flex-wrap sm:items-end sm:justify-between sm:p-6 dark:border-gray-800">
            <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
                <x-common.data-table.per-page-select :options="$perPageOptions" />
                @if ($type)
                    <button type="button" wire:click="resetFilters" class="text-sm text-gray-500 hover:underline sm:mb-3 dark:text-gray-400">Reset filter</button>
                @endif
            </div>

            <a href="{{ route('admin.locations.create') }}"
                class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 sm:w-auto">
                Tambah Lokasi Kantor
            </a>
        </div>

        <div class="max-w-full overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[800px]">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <x-common.data-table.th field="name" label="Nama" :sort="$sort" :direction="$direction" />
                        <x-common.data-table.th field="type" label="Tipe" :sort="$sort" :direction="$direction" :active="$type !== ''">
                            <select wire:model.live="type"
                                class="dark:bg-dark-900 shadow-theme-xs w-full rounded-lg border border-gray-300 bg-transparent px-2 py-1.5 text-xs text-gray-800 dark:border-gray-700 dark:text-white/90">
                                <option value="">- Semua -</option>
                                <option value="RADIUS">Radius</option>
                                <option value="POLYGON">Poligon</option>
                            </select>
                        </x-common.data-table.th>
                        <x-common.data-table.th label="Koordinat" />
                        <x-common.data-table.th label="Cakupan" />
                        <x-common.data-table.th label="Aksi" />
                    </tr>
                </thead>
                <tbody>
                    @forelse ($locations as $location)
                        <tr wire:key="location-{{ $location->id }}" class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-5 py-4 sm:px-6"><p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $location->name }}</p></td>
                            <td class="px-5 py-4 sm:px-6"><x-ui.badge color="primary">{{ $location->type === 'POLYGON' ? 'Poligon' : 'Radius' }}</x-ui.badge></td>
                            <td class="px-5 py-4 sm:px-6"><p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $location->lat }}, {{ $location->long }}</p></td>
                            <td class="px-5 py-4 sm:px-6">
                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                    @if ($location->type === 'POLYGON')
                                        {{ count($location->polygon ?? []) }} titik
                                    @else
                                        {{ $location->radius_meters }} meter
                                    @endif
                                </p>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('admin.locations.edit', $location) }}" class="text-sm text-brand-500 hover:underline">Edit</a>
                                    <button type="button" wire:click="delete({{ $location->id }})" wire:confirm="Hapus lokasi kantor ini?"
                                        class="text-sm text-error-500 hover:underline">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada lokasi kantor.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
            {{ $locations->links() }}
        </div>
    </div>
</div>

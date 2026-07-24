<div>
    <x-ui.flash-success />

    <div class="mb-4 flex justify-end">
        @unless ($showCreateForm)
            <button type="button" wire:click="openCreateForm"
                class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 sm:w-auto">
                Tambah Kegiatan
            </button>
        @endunless
    </div>

    @if ($showCreateForm)
        <div class="mb-6 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="mb-4 text-base font-medium text-gray-800 dark:text-white/90">Tambah Kegiatan / Apel</h3>
            <form wire:submit="store" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nama Kegiatan</label>
                    <input type="text" wire:model="newName" list="mandatory-event-name-history" placeholder="mis. Apel Cabang SIM Juli"
                        class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90" />
                    <datalist id="mandatory-event-name-history">
                        @foreach ($nameHistory as $name)
                            <option value="{{ $name }}"></option>
                        @endforeach
                    </datalist>
                    @error('newName') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                </div>

                <div wire:ignore x-on:date-change="$wire.set('newDate', $event.detail.dateStr)">
                    <x-form.date-picker id="new-event-date" label="Tanggal" dateFormat="Y-m-d"
                        placeholder="Pilih atau ketik tanggal" :allowInput="true" />
                    @error('newDate') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                </div>

                <div wire:ignore x-on:date-change="$wire.set('newTime', $event.detail.dateStr)">
                    <x-form.date-picker id="new-event-time" label="Jam Mulai (opsional)" dateFormat="H:i"
                        placeholder="Pilih atau ketik jam" :enableTime="true" :noCalendar="true" :allowInput="true" />
                    @error('newTime') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Presensi Peserta</label>
                    <div class="inline-flex rounded-lg border border-gray-300 p-1 dark:border-gray-700">
                        <button type="button" wire:click="setGroupingMode('per_bagian')"
                            class="rounded-md px-4 py-2 text-sm font-medium transition {{ $newGroupingMode === 'per_bagian' ? 'bg-brand-500 text-white' : 'text-gray-600 dark:text-gray-300' }}">
                            Per Bagian/Cabang
                        </button>
                        <button type="button" wire:click="setGroupingMode('gabung')"
                            class="rounded-md px-4 py-2 text-sm font-medium transition {{ $newGroupingMode === 'gabung' ? 'bg-brand-500 text-white' : 'text-gray-600 dark:text-gray-300' }}">
                            Gabung Semua
                        </button>
                    </div>
                    <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500">Pilih "Gabung Semua" untuk kegiatan yang pesertanya diacak lintas bagian/cabang, supaya daftar presensi tidak dipisah per unit.</p>
                    @error('newGroupingMode') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-full grid grid-cols-1 gap-3 rounded-lg border border-gray-100 p-4 sm:grid-cols-2 dark:border-gray-800">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tambah dari Departemen/Cabang</label>
                        <select wire:change="addDepartmentMembers($event.target.value)"
                            class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">
                            <option value="">- Pilih departemen/cabang -</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }} ({{ $dept->type }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Muat dari Preset</label>
                        <select wire:change="loadPreset($event.target.value)"
                            class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">
                            <option value="">- Pilih preset -</option>
                            @foreach ($presets as $preset)
                                <option value="{{ $preset->id }}">{{ $preset->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2 flex flex-wrap items-center gap-3">
                        <button type="button" wire:click="selectAllUsers"
                            class="text-sm text-brand-500 hover:underline">+ Pilih Semua Pegawai</button>
                        <button type="button" wire:click="selectAllPejabat"
                            class="text-sm text-brand-500 hover:underline">+ Pilih Semua Pejabat</button>
                        <button type="button" wire:click="selectAllKepalaSeksi"
                            class="text-sm text-brand-500 hover:underline">+ Pilih Semua Kepala Seksi</button>
                        <button type="button" wire:click="selectAllKabagCabangStaffAhli"
                            class="text-sm text-brand-500 hover:underline">+ Pilih Semua Kepala Bagian/Cabang/Staf Ahli</button>
                        @if ($selectedUserIds)
                            <button type="button" wire:click="clearParticipants"
                                class="text-sm text-gray-500 hover:underline dark:text-gray-400">Kosongkan Peserta</button>
                        @endif
                    </div>

                    <div class="sm:col-span-2">
                        <x-form.person-select name="selectedUserIds" :options="$allUsers" multiple start-expanded
                            label="Pilih Peserta"
                            wire:key="participants-{{ $participantsVersion }}" />
                        @error('selectedUserIds') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2 flex items-end gap-2">
                        <div class="flex-1">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Simpan Peserta Terpilih sebagai Preset</label>
                            <input type="text" wire:model="presetName" placeholder="mis. Apel Cabang SIM"
                                class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90" />
                            @error('presetName') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>
                        <button type="button" wire:click="savePreset"
                            class="h-11 shrink-0 rounded-lg border border-gray-300 px-4 text-sm text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                            Simpan Preset
                        </button>
                    </div>
                </div>

                <div class="col-span-full flex justify-end gap-3">
                    <button type="button" wire:click="cancelCreateForm"
                        class="inline-flex items-center justify-center rounded-lg bg-white px-5 py-3.5 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700">
                        Batal
                    </button>
                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                        Simpan Kegiatan
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]" wire:loading.class="opacity-60">
        <div class="flex flex-col gap-3 border-b border-gray-100 p-4 sm:flex-row sm:flex-wrap sm:items-end sm:justify-between sm:p-6 dark:border-gray-800">
            <x-common.data-table.per-page-select :options="$perPageOptions" />
        </div>

        <div class="max-w-full overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[700px]">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <x-common.data-table.th field="date" label="Tanggal" :sort="$sort" :direction="$direction" />
                        <x-common.data-table.th field="name" label="Nama Kegiatan" :sort="$sort" :direction="$direction" />
                        <x-common.data-table.th field="time" label="Jam" :sort="$sort" :direction="$direction" />
                        <x-common.data-table.th label="Peserta" />
                        <x-common.data-table.th label="Aksi" />
                    </tr>
                </thead>
                <tbody>
                    @forelse ($events as $event)
                        <tr wire:key="event-{{ $event->id }}" class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-5 py-4 sm:px-6"><p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $event->date->format('d-m-Y') }}</p></td>
                            <td class="px-5 py-4 sm:px-6"><p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $event->name }}</p></td>
                            <td class="px-5 py-4 sm:px-6"><p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $event->time ? \Illuminate\Support\Carbon::parse($event->time)->format('H:i') : '-' }}</p></td>
                            <td class="px-5 py-4 sm:px-6"><x-ui.badge color="primary">{{ $event->participants_count }} orang</x-ui.badge></td>
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('admin.mandatory-events.show', $event) }}" class="text-sm text-brand-500 hover:underline">Presensi</a>
                                    <button type="button" x-data
                                        @click="$store.confirmDialog.open({
                                            title: 'Hapus Kegiatan',
                                            message: 'Hapus kegiatan ' + @js($event->name) + ' (' + @js($event->date->format('d-m-Y')) + ') beserta seluruh data presensinya?',
                                            onConfirm: () => $wire.delete({{ $event->id }})
                                        })"
                                        class="text-sm text-error-500 hover:underline">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada kegiatan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
            {{ $events->links() }}
        </div>
    </div>
</div>

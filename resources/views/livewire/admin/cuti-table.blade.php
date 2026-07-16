<div>
    @if (session('success'))
        <div class="mb-6">
            <x-ui.alert variant="success" :message="session('success')" />
        </div>
    @endif

    <div class="mb-4 flex justify-end">
        @unless ($showCreateForm)
            <button type="button" wire:click="openCreateForm"
                class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 sm:w-auto">
                Input Cuti Manual
            </button>
        @endunless
    </div>

    @if ($showCreateForm)
        <div class="mb-6 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="mb-4 text-base font-medium text-gray-800 dark:text-white/90">Input Cuti</h3>
            <form wire:submit="createCuti" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Pegawai</label>
                    <x-form.person-select name="newUserId" :options="$employees" wire:key="new-user-id-picker" />
                </div>

                <div>
                    <div wire:ignore x-on:date-change="$wire.set('newStartDate', $event.detail.dateStr)">
                        <x-form.date-picker id="cuti-start-date" label="Tanggal Mulai" :default-date="$newStartDate ?: null" date-format="Y-m-d" />
                    </div>
                    @error('newStartDate') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <div wire:ignore x-on:date-change="$wire.set('newEndDate', $event.detail.dateStr)">
                        <x-form.date-picker id="cuti-end-date" label="Tanggal Selesai" :default-date="$newEndDate ?: null" date-format="Y-m-d" />
                    </div>
                    @error('newEndDate') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Alasan</label>
                    <textarea wire:model="newReason" rows="2"
                        class="dark:bg-dark-900 shadow-theme-xs w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90"></textarea>
                    @error('newReason') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Status</label>
                    <select wire:model="newStatus"
                        class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">
                        <option value="APPROVED">Disetujui</option>
                        <option value="PENDING">Pending</option>
                        <option value="REJECTED">Ditolak</option>
                    </select>
                    @error('newStatus') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Scan Surat (opsional, PDF)</label>
                    <input type="file" wire:model="newAttachment" accept="application/pdf"
                        class="dark:bg-dark-900 shadow-theme-xs w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90" />
                    @error('newAttachment') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-full flex justify-end gap-3">
                    <button type="button" wire:click="cancelCreateForm"
                        class="inline-flex items-center justify-center rounded-lg bg-white px-5 py-3.5 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700">
                        Batal
                    </button>
                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    @endif

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
                        <x-common.data-table.th label="Sumber" />
                        <x-common.data-table.th field="status" label="Status" :sort="$sort" :direction="$direction" :active="$status !== ''">
                            <select wire:model.live="status"
                                class="dark:bg-dark-900 shadow-theme-xs w-full rounded-lg border border-gray-300 bg-transparent px-2 py-1.5 text-xs text-gray-800 dark:border-gray-700 dark:text-white/90">
                                <option value="">- Semua -</option>
                                @foreach (['PENDING', 'APPROVED', 'REJECTED'] as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                        </x-common.data-table.th>
                        <x-common.data-table.th label="Aksi" />
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cutiList as $cuti)
                        <tr wire:key="cuti-{{ $cuti->id }}" class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-5 py-4 sm:px-6">
                                <p class="text-gray-800 text-theme-sm dark:text-white/90">{{ $cuti->user->name }}</p>
                                <p class="text-gray-400 text-theme-xs">{{ $cuti->reason }}</p>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                    {{ $cuti->start_date->format('d M Y') }} - {{ $cuti->end_date->format('d M Y') }}
                                </p>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <x-ui.badge :color="$cuti->source === 'APP' ? 'primary' : 'light'">{{ $cuti->source }}</x-ui.badge>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <x-ui.badge :color="match ($cuti->status) {
                                    'APPROVED' => 'success',
                                    'REJECTED' => 'error',
                                    default => 'warning',
                                }">
                                    {{ $cuti->status }}
                                </x-ui.badge>
                                @if ($cuti->status === 'REJECTED' && $cuti->rejection_reason)
                                    <p class="mt-1 text-gray-400 text-theme-xs">{{ $cuti->rejection_reason }}</p>
                                @endif
                                @if ($cuti->approver)
                                    <p class="mt-1 text-gray-400 text-theme-xs">oleh {{ $cuti->approver->name }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                @if ($cuti->status === 'PENDING' && $approvingId === $cuti->id)
                                    <form wire:submit="confirmApprove" class="flex items-center gap-2">
                                        <input type="file" wire:model="approveAttachment" accept="application/pdf" class="text-xs" />
                                        <button type="submit" class="text-sm text-success-500 hover:underline">Simpan</button>
                                        <button type="button" wire:click="cancelApprove" class="text-sm text-gray-400 hover:underline">Batal</button>
                                    </form>
                                    @error('approveAttachment') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                                @elseif ($cuti->status === 'PENDING' && $rejectingId === $cuti->id)
                                    <form wire:submit="confirmReject" class="flex items-center gap-2">
                                        <input type="text" wire:model="rejectReason" placeholder="Alasan tolak"
                                            class="dark:bg-dark-900 h-9 w-40 rounded-lg border border-gray-300 bg-transparent px-2 text-xs text-gray-800 dark:border-gray-700 dark:text-white/90" />
                                        <button type="submit" class="text-sm text-error-500 hover:underline">Simpan</button>
                                        <button type="button" wire:click="cancelReject" class="text-sm text-gray-400 hover:underline">Batal</button>
                                    </form>
                                    @error('rejectReason') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                                @elseif ($cuti->status === 'PENDING')
                                    <div class="flex items-center gap-3">
                                        <button type="button" wire:click="openApprove({{ $cuti->id }})" class="text-sm text-success-500 hover:underline">Approve</button>
                                        <button type="button" wire:click="openReject({{ $cuti->id }})" class="text-sm text-error-500 hover:underline">Tolak</button>
                                    </div>
                                @else
                                    <span class="text-sm text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
            {{ $cutiList->links() }}
        </div>
    </div>
</div>

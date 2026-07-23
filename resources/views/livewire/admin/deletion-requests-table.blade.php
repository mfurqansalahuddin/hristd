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
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="Nama atau email"
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
                        <x-common.data-table.th label="Nama & Email" />
                        <x-common.data-table.th label="Alasan" />
                        <x-common.data-table.th field="created_at" label="Diajukan" :sort="$sort" :direction="$direction" />
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
                    @foreach ($deletionRequests as $deletionRequest)
                        <tr wire:key="deletion-request-{{ $deletionRequest->id }}" class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-5 py-4 sm:px-6">
                                <p class="text-gray-800 text-theme-sm dark:text-white/90">{{ $deletionRequest->name }}</p>
                                <p class="text-gray-400 text-theme-xs">{{ $deletionRequest->email }}</p>
                                @unless ($deletionRequest->user)
                                    <p class="mt-1 text-theme-xs text-warning-500">Tidak ditemukan user dengan email ini</p>
                                @endunless
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $deletionRequest->reason ?: '-' }}</p>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $deletionRequest->created_at->format('d M Y') }}</p>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <x-ui.badge :color="match ($deletionRequest->status) {
                                    'APPROVED' => 'success',
                                    'REJECTED' => 'error',
                                    default => 'warning',
                                }">
                                    {{ $deletionRequest->status }}
                                </x-ui.badge>
                                @if ($deletionRequest->status === 'REJECTED' && $deletionRequest->rejection_reason)
                                    <p class="mt-1 text-gray-400 text-theme-xs">{{ $deletionRequest->rejection_reason }}</p>
                                @endif
                                @if ($deletionRequest->reviewer)
                                    <p class="mt-1 text-gray-400 text-theme-xs">oleh {{ $deletionRequest->reviewer->name }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                @if ($deletionRequest->status === 'PENDING' && $approvingId === $deletionRequest->id)
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Hapus akun & seluruh data?</span>
                                        <button type="button" wire:click="confirmApprove" class="text-sm text-success-500 hover:underline">Ya, hapus</button>
                                        <button type="button" wire:click="cancelApprove" class="text-sm text-gray-400 hover:underline">Batal</button>
                                    </div>
                                @elseif ($deletionRequest->status === 'PENDING' && $rejectingId === $deletionRequest->id)
                                    <form wire:submit="confirmReject" class="flex items-center gap-2">
                                        <input type="text" wire:model="rejectReason" placeholder="Alasan tolak"
                                            class="dark:bg-dark-900 h-9 w-40 rounded-lg border border-gray-300 bg-transparent px-2 text-xs text-gray-800 dark:border-gray-700 dark:text-white/90" />
                                        <button type="submit" class="text-sm text-error-500 hover:underline">Simpan</button>
                                        <button type="button" wire:click="cancelReject" class="text-sm text-gray-400 hover:underline">Batal</button>
                                    </form>
                                    @error('rejectReason') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                                @elseif ($deletionRequest->status === 'PENDING')
                                    <div class="flex items-center gap-3">
                                        <button type="button" wire:click="openApprove({{ $deletionRequest->id }})" class="text-sm text-success-500 hover:underline">Approve</button>
                                        <button type="button" wire:click="openReject({{ $deletionRequest->id }})" class="text-sm text-error-500 hover:underline">Tolak</button>
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
            {{ $deletionRequests->links() }}
        </div>
    </div>
</div>

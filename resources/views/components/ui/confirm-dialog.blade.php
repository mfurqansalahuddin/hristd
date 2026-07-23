{{--
    Global confirm dialog, mounted once in layouts/app.blade.php.
    Trigger from anywhere (inside a Livewire component root or not) with:

        <button type="button" x-data
            @click="$store.confirmDialog.open({
                title: 'Hapus Pegawai',
                message: 'Hapus pegawai ini?',
                onConfirm: () => $wire.delete({{ $employee->id }})
            })">Hapus</button>

    Not wire:confirm/native confirm() — those can be silently suppressed by the browser.
--}}
<div x-data
    x-show="$store.confirmDialog.isOpen"
    x-cloak
    x-on:keydown.escape.window="$store.confirmDialog.cancel()"
    class="fixed inset-0 z-99999 flex items-center justify-center overflow-y-auto p-5">

    <!-- Backdrop -->
    <div @click="$store.confirmDialog.cancel()" class="fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[32px]"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    </div>

    <!-- Dialog -->
    <div @click.stop class="relative w-full max-w-md rounded-3xl bg-white p-6 shadow-theme-lg dark:bg-gray-900"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95">

        <h3 class="mb-2 text-lg font-semibold text-gray-800 dark:text-white/90" x-text="$store.confirmDialog.title"></h3>
        <p class="mb-6 text-sm text-gray-500 dark:text-gray-400" x-text="$store.confirmDialog.message"></p>

        <div class="flex items-center justify-end gap-3">
            <button type="button" @click="$store.confirmDialog.cancel()"
                class="inline-flex flex-1 items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]"
                x-text="$store.confirmDialog.cancelText"></button>
            <button type="button" @click="$store.confirmDialog.confirm()"
                class="inline-flex flex-1 items-center justify-center rounded-lg border px-5 py-3 text-sm font-medium shadow-theme-xs"
                :class="$store.confirmDialog.variant === 'danger'
                    ? 'border-error-500 bg-error-500 text-white hover:border-error-600 hover:bg-error-600'
                    : 'border-brand-500 bg-brand-500 text-white hover:border-brand-600 hover:bg-brand-600'"
                x-text="$store.confirmDialog.confirmText"></button>
        </div>
    </div>
</div>

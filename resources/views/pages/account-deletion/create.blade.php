@extends('layouts.fullscreen-layout')

@section('content')
    <div class="relative z-1 bg-white p-6 dark:bg-gray-900">
        <div class="relative flex min-h-screen w-full flex-col items-center justify-center py-10">
            <div class="mx-auto w-full max-w-md">
                <div class="mb-8 flex flex-col items-center text-center">
                    <img src="{{ asset('Logo%20TD%20nobg.png') }}" alt="Logo Perumdam Tirta Daroy" class="mb-4 h-16 w-auto">
                    <h1 class="text-title-sm sm:text-title-md mb-2 font-semibold text-gray-800 dark:text-white/90">
                        Ajukan Penghapusan Akun
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Isi formulir ini untuk meminta penghapusan akun HRIS TD beserta seluruh data terkait.
                        Permintaan akan diverifikasi dan diproses oleh HR.
                    </p>
                </div>

                @if (session('success'))
                    <x-ui.flash-success />
                @else
                    <form action="{{ route('account-deletion.store') }}" method="POST" class="space-y-5">
                        @csrf
                        <x-form.input label="Nama Lengkap" name="name" required />
                        <x-form.input label="Email Akun" type="email" name="email" placeholder="nama@perumdamtirtadaroy.id" required />

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Alasan (opsional)
                            </label>
                            <textarea name="reason" rows="3"
                                class="dark:bg-dark-900 shadow-theme-xs w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">{{ old('reason') }}</textarea>
                            @error('reason') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                        </div>

                        <button type="submit"
                            class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 flex w-full items-center justify-center rounded-lg px-4 py-3 text-sm font-medium text-white transition">
                            Ajukan Penghapusan
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection

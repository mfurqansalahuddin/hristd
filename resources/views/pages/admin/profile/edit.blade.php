@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" />

    @if (session('success'))
        <div class="mb-6">
            <x-ui.alert variant="success" :message="session('success')" />
        </div>
    @endif

    <x-common.component-card :title="$title">
        <form method="POST" enctype="multipart/form-data" action="{{ route('admin.profile.update') }}">
            @csrf
            @method('PUT')

            <div class="mb-5 flex items-center gap-4">
                <img src="{{ auth()->user()->photoUrl() }}" alt="Foto profil" class="h-16 w-16 rounded-full object-cover">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Foto Profil</label>
                    <input type="file" name="photo" accept="image/*"
                        class="block text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-brand-500 dark:text-gray-400 dark:file:bg-white/5 dark:file:text-brand-400">
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-form.input name="name" label="Nama" :value="auth()->user()->name" required />
                <x-form.input name="email" type="email" label="Email" :value="auth()->user()->email" required />
                <x-form.input name="password" type="password" label="Kata Sandi Baru"
                    placeholder="Kosongkan jika tidak diubah" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                    Simpan
                </button>
            </div>
        </form>
    </x-common.component-card>
@endsection

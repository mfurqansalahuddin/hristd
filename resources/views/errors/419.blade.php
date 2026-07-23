@extends('layouts.fullscreen-layout')

@section('content')
    @php
        $currentYear = date('Y');
    @endphp
    <div class="relative flex flex-col items-center justify-center min-h-screen p-6 overflow-hidden z-1">
        <x-common.common-grid-shape />
        {{-- ponytail: no dedicated 419 illustration exists yet, reusing the 404 art --}}
        <div class="mx-auto w-full max-w-[242px] text-center sm:max-w-[472px]">
            <h1 class="mb-8 font-bold text-gray-800 text-title-md dark:text-white/90 xl:text-title-2xl">
                ERROR
            </h1>

            <img src="/images/error/404.svg" alt="419" class="dark:hidden" />
            <img src="/images/error/404-dark.svg" alt="419" class="hidden dark:block" />

            <p class="mt-10 mb-6 text-base text-gray-700 dark:text-gray-400 sm:text-lg">
                Sesi Anda telah berakhir. Silakan masuk kembali.
            </p>

            <x-errors.back-button />
        </div>

        <p class="absolute text-sm text-center text-gray-500 -translate-x-1/2 bottom-6 left-1/2 dark:text-gray-400">
            &copy; {{ $currentYear }} - Perumdam Tirta Daroy
        </p>
    </div>
@endsection

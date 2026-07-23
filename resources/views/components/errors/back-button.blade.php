@props(['forbidden' => false])

@php
    $isGuest = auth()->guest();
    $label = $isGuest || $forbidden ? 'Kembali ke Login' : 'Kembali ke Dashboard';
@endphp

@if (! $isGuest && $forbidden)
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit"
            class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-3.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
            {{ $label }}
        </button>
    </form>
@else
    <a href="{{ $isGuest ? route('signin') : route('admin.dashboard') }}"
        class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-3.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
        {{ $label }}
    </a>
@endif

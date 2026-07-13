@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Maps" />
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Map View -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-5 flex items-start justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                        Map View
                    </h3>
                    <p class="text-theme-sm mt-1 text-gray-500 dark:text-gray-400">
                        Clear view of locations at a glance
                    </p>
                </div>
            </div>

            <div class="relative z-0 overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800">
                <p id="mapLocationView3Desc" class="sr-only">
                    Interactive map showing the Washington D.C. metro area. Use arrow keys to pan and plus/minus keys to zoom.
                </p>
                <div id="mapLocationView3" class="h-[300px] w-full" role="application" aria-label="Interactive location map" aria-describedby="mapLocationView3Desc" tabindex="0"></div>

                <!-- Zoom Controls -->
                <div class="absolute top-3 right-3 z-[999]">
                    <div class="flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                        <button id="mapLocationZoomIn3" class="flex h-9 w-9 items-center justify-center border-b border-gray-200 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white" aria-label="Zoom in">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M8 3.33334V12.6667M3.33334 8H12.6667" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                        <button id="mapLocationZoomOut3" class="flex h-9 w-9 items-center justify-center text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white" aria-label="Zoom out">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3.33334 8H12.6667" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Map 2 -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-5 flex items-start justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                        Map 2
                    </h3>
                    <p class="text-theme-sm mt-1 text-gray-500 dark:text-gray-400">
                        Clear view of locations at a glance
                    </p>
                </div>
            </div>
            <div class="mt-5">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3650.5145053176284!2d90.42105717591272!3d23.800296778636472!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3755c7e9f37a5a3d%3A0x41d7d1d02e1ed0e4!2sPimjo!5e0!3m2!1sen!2sbd!4v1751871078440!5m2!1sen!2sbd" width="303" height="300" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" class="!w-full rounded-xl border border-gray-200 grayscale dark:border-gray-800"></iframe>
            </div>
        </div>

        <!-- Washington D.C. Region -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-5 flex items-start justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                        Washington D.C. Region
                    </h3>
                    <p class="text-theme-sm mt-1 text-gray-500 dark:text-gray-400">
                        Interactive map with Home and Office Pinned
                    </p>
                </div>
            </div>

            <div class="relative z-0 overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800">
                <div id="mapLocationView" class="h-[300px] w-full"></div>

                <!-- Zoom Controls -->
                <div class="absolute top-3 right-3 z-[999]">
                    <div class="flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                        <button id="mapLocationZoomIn" class="flex h-9 w-9 items-center justify-center border-b border-gray-200 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white" aria-label="Zoom in">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M8 3.33334V12.6667M3.33334 8H12.6667" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                        <button id="mapLocationZoomOut" class="flex h-9 w-9 items-center justify-center text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white" aria-label="Zoom out">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3.33334 8H12.6667" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

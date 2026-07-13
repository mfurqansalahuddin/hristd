@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Bar chart" />
    <div class="space-y-6">
        <x-common.component-card title="Bar chart 1">
            <!-- ====== Bar Chart One Start -->
            <div class="custom-scrollbar max-w-full overflow-x-auto">
                <div id="chartOne" class="min-w-[1000px]"></div>
            </div>
            <!-- ====== Bar Chart One End -->
        </x-common.component-card>

        <x-common.component-card title="Bar chart 2">
            <!-- ====== Bar Chart Two Start -->
            <div class="custom-scrollbar max-w-full overflow-x-auto">
                <div id="chartSix" class="min-w-[1000px]"></div>
            </div>
            <!-- ====== Bar Chart Two End -->
        </x-common.component-card>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="px-6 py-5">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        Bar Chart 3
                    </h3>
                </div>
                <div class="border-t border-gray-100 p-5 sm:p-6 dark:border-gray-800">
                    <!-- ====== Bar Chart Two Start -->
                    <div>
                        <div id="chartTwentySeven"></div>
                    </div>
                    <!-- ====== Bar Chart Two End -->
                </div>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="px-6 py-5">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        Horizontal Bar Chart
                    </h3>
                </div>
                <div class="border-t border-gray-100 p-5 sm:p-6 dark:border-gray-800">
                    <!-- ====== Bar Chart Two Start -->
                    <div class="-ml-1">
                        <div id="chartThirty"></div>
                    </div>
                    <!-- ====== Bar Chart Two End -->
                </div>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="px-6 py-5">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        Double Bar Chart
                    </h3>
                </div>
                <div class="border-t border-gray-100 p-5 sm:p-6 dark:border-gray-800">
                    <!-- ====== Double Bar Chart Start -->
                    <div class="-ml-1">
                        <div id="chartThirtyTwo"></div>
                    </div>
                    <!-- ====== Double Bar Chart End -->
                </div>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="px-6 py-5">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        Horizontal Grouped Bar Chart
                    </h3>
                </div>
                <div class="border-t border-gray-100 p-5 sm:p-6 dark:border-gray-800">
                    <!-- ====== Bar Chart ThirtyFour Start -->
                    <div>
                        <div id="chartThirtyOne"></div>
                    </div>
                    <!-- ====== Bar Chart ThirtyFour End -->
                </div>
            </div>
        </div>
    </div>
@endsection

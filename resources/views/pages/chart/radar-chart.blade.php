@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Radar chart" />
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <x-common.component-card title="Radar Chart 1">
            <!-- ====== Radar Chart ThirtySeven Start -->
            <div class="flex justify-center">
                <div id="chartThirtySeven" class="chartDarkStyle w-full"></div>
            </div>
            <!-- ====== Radar Chart ThirtySeven End -->
        </x-common.component-card>

        <x-common.component-card title="Radar Chart 2">
            <!-- ====== Radar Chart ThirtyEight Start -->
            <div class="flex justify-center">
                <div id="chartThirtyEight" class="chartDarkStyle w-full"></div>
            </div>
            <!-- ====== Radar Chart ThirtyEight End -->
        </x-common.component-card>

        <x-common.component-card title="Radar Chart 3">
            <!-- ====== Radar Chart ThirtyNine Start -->
            <div class="flex justify-center">
                <div id="chartThirtyNine" class="chartDarkStyle w-full"></div>
            </div>
            <!-- ====== Radar Chart ThirtyNine End -->
        </x-common.component-card>
    </div>
@endsection

@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" :items="[['label' => 'Manajemen Kantor', 'url' => route('admin.locations.index')]]" />

    <x-common.component-card :title="$title">
        <form method="POST"
            action="{{ $location->exists ? route('admin.locations.update', $location) : route('admin.locations.store') }}"
            x-data="{ type: '{{ old('type', $location->type ?? 'RADIUS') }}' }">
            @csrf
            @if ($location->exists)
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-form.input name="name" label="Nama Lokasi"
                        placeholder="mis. Kantor Pusat, Cabang Ulee Kareng, WTP Lambaro, Balai Kota (apel gabungan)"
                        :value="$location->name" required />
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tipe Cakupan</label>
                    <div class="flex gap-6">
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-400">
                            <input type="radio" name="type" value="RADIUS" x-model="type" required>
                            Radius (titik pusat + jarak)
                        </label>
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-400">
                            <input type="radio" name="type" value="POLYGON" x-model="type" required>
                            Poligon (gambar batas area di peta)
                        </label>
                    </div>
                </div>

                <div>
                    <x-form.input id="location-lat" name="lat" type="number" label="Latitude" :value="$location->lat" required
                        step="any" placeholder="Klik peta untuk mengisi otomatis" />
                </div>
                <div>
                    <x-form.input id="location-long" name="long" type="number" label="Longitude" :value="$location->long" required
                        step="any" placeholder="Klik peta untuk mengisi otomatis" />
                </div>

                <div x-show="type === 'RADIUS'" x-cloak>
                    <x-form.input id="location-radius" name="radius_meters" type="number" label="Radius (meter)"
                        :value="$location->radius_meters ?? 100" min="10" />
                </div>

                <div x-show="type === 'POLYGON'" x-cloak>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Titik Poligon</label>
                    <div class="flex h-11 items-center gap-4">
                        <span id="polygon-point-count" class="text-sm text-gray-500 dark:text-gray-400">{{ count($location->polygon ?? []) }} titik</span>
                        <button type="button" id="polygon-undo" class="text-sm text-brand-500 hover:underline">Hapus titik terakhir</button>
                        <button type="button" id="polygon-reset" class="text-sm text-error-500 hover:underline">Reset</button>
                    </div>
                </div>

                <input type="hidden" id="location-polygon" name="polygon" value='{{ json_encode($location->polygon ?? []) }}'>
            </div>

            <div class="mt-5">
                <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                    <span x-show="type === 'RADIUS'">Klik di peta untuk menandai titik pusat kantor (bisa juga digeser markernya).</span>
                    <span x-show="type === 'POLYGON'" x-cloak>Klik di peta untuk menambah titik batas area, terus sampai membentuk poligon lokasi kantor.</span>
                </p>
                <div id="officeLocationMap" class="z-1 h-[400px] w-full rounded-xl border border-gray-200 dark:border-gray-800"></div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('admin.locations.index') }}"
                    class="inline-flex items-center justify-center rounded-lg bg-white px-5 py-3.5 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700">
                    Batal
                </a>
                <button type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                    Simpan
                </button>
            </div>
        </form>
    </x-common.component-card>
@endsection

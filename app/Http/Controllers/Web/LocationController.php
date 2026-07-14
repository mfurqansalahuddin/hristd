<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\LocationRequest;
use App\Models\Location;

class LocationController extends Controller
{
    public function index()
    {
        return view('pages.admin.locations.index', [
            'title' => 'Manajemen Kantor',
        ]);
    }

    public function create()
    {
        return view('pages.admin.locations.form', [
            'title' => 'Tambah Lokasi Kantor',
            'location' => new Location,
        ]);
    }

    public function store(LocationRequest $request)
    {
        Location::create($request->validated());

        return redirect()->route('admin.locations.index')->with('success', 'Lokasi kantor berhasil ditambahkan.');
    }

    public function edit(Location $location)
    {
        return view('pages.admin.locations.form', [
            'title' => 'Edit Lokasi Kantor',
            'location' => $location,
        ]);
    }

    public function update(LocationRequest $request, Location $location)
    {
        $location->update($request->validated());

        return redirect()->route('admin.locations.index')->with('success', 'Lokasi kantor berhasil diperbarui.');
    }

    public function destroy(Location $location)
    {
        $location->delete();

        return redirect()->route('admin.locations.index')->with('success', 'Lokasi kantor berhasil dihapus.');
    }
}

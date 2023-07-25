<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;

class DeviceController extends Controller
{
    // Menampilkan daftar device
    public function index()
    {
        $devices = Device::all();
        return view('devices.index', compact('devices'));
    }

    // Menampilkan form tambah device
    public function create()
    {
        return view('devices.create');
    }

    // Menyimpan device baru ke database
    public function store(Request $request)
    {
        $device = new Device();
        $device->nama = $request->input('nama');
        $device->no_sn = $request->input('no_sn');
        $device->lokasi = $request->input('lokasi');
        $device->save();

        return redirect()->route('devices.index')->with('success', 'Device berhasil ditambahkan!');
    }

    // Menampilkan detail device
    public function show($id)
    {
        $device = Device::find($id);
        return view('devices.show', compact('device'));
    }

    // Menampilkan form edit device
    public function edit($id)
    {
        $device = Device::find($id);
        return view('devices.edit', compact('device'));
    }

    // Mengupdate device ke database
    public function update(Request $request, $id)
    {
        $device = Device::find($id);
        $device->nama = $request->input('nama');
        $device->no_sn = $request->input('no_sn');
        $device->lokasi = $request->input('lokasi');
        $device->save();

        return redirect()->route('devices.index')->with('success', 'Device berhasil diupdate!');
    }

    // Menghapus device dari database
    public function destroy($id)
    {
        $device = Device::find($id);
        $device->delete();

        return redirect()->route('devices.index')->with('success', 'Device berhasil dihapus!');
    }
}

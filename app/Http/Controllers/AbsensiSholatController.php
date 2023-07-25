<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AbsensiSholat;

class AbsensiSholatController extends Controller
{
    // Menampilkan daftar absensi sholat
    public function index()
    {
        $absensiSholat = AbsensiSholat::all();
        return view('absensi_sholat.index', compact('absensiSholat'));
    }

    // Menampilkan form tambah absensi sholat
    public function create()
    {
        // Anda bisa mengambil data jadwal sholat dan devices untuk digunakan dalam dropdown atau form
        // $jadwalSholat = ...;
        // $devices = ...;
        // return view('absensi_sholat.create', compact('jadwalSholat', 'devices'));
        return view('absensi_sholat.create');
    }

    // Menyimpan absensi sholat baru ke database
    public function store(Request $request)
    {
        $absensiSholat = new AbsensiSholat();
        $absensiSholat->waktu = $request->input('waktu');
        $absensiSholat->tgl = $request->input('tgl');
        $absensiSholat->id_jadwal_sholat = $request->input('id_jadwal_sholat');
        $absensiSholat->id_devices = $request->input('id_devices');
        $absensiSholat->nis_santri = $request->input('nis_santri');
        $absensiSholat->save();

        return redirect()->route('absensi_sholat.index')->with('success', 'Absensi Sholat berhasil ditambahkan!');
    }

    // Menampilkan detail absensi sholat
    public function show($id)
    {
        $absensiSholat = AbsensiSholat::find($id);
        return view('absensi_sholat.show', compact('absensiSholat'));
    }

    // Menampilkan form edit absensi sholat
    public function edit($id)
    {
        $absensiSholat = AbsensiSholat::find($id);
        // $jadwalSholat = ...;
        // $devices = ...;
        // return view('absensi_sholat.edit', compact('absensiSholat', 'jadwalSholat', 'devices'));
        return view('absensi_sholat.edit', compact('absensiSholat'));
    }

    // Mengupdate absensi sholat ke database
    public function update(Request $request, $id)
    {
        $absensiSholat = AbsensiSholat::find($id);
        $absensiSholat->waktu = $request->input('waktu');
        $absensiSholat->tgl = $request->input('tgl');
        $absensiSholat->id_jadwal_sholat = $request->input('id_jadwal_sholat');
        $absensiSholat->id_devices = $request->input('id_devices');
        $absensiSholat->nis_santri = $request->input('nis_santri');
        $absensiSholat->save();

        return redirect()->route('absensi_sholat.index')->with('success', 'Absensi Sholat berhasil diupdate!');
    }

    // Menghapus absensi sholat dari database
    public function destroy($id)
    {
        $absensiSholat = AbsensiSholat::find($id);
        $absensiSholat->delete();

        return redirect()->route('absensi_sholat.index')->with('success', 'Absensi Sholat berhasil dihapus!');
    }
}

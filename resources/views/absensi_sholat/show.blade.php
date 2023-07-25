@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Detail Absensi Sholat</h2>
        <p>Waktu: {{ $absensiSholat->waktu }}</p>
        <p>Tanggal: {{ $absensiSholat->tgl }}</p>
        <p>Jadwal Sholat: {{ $absensiSholat->id_jadwal_sholat }}</p>
        <p>Device: {{ $absensiSholat->id_devices }}</p>
        <p>NIS Santri: {{ $absensiSholat->nis_santri }}</p>
        <a href="{{ route('absensi_sholat.edit', $absensiSholat->id) }}" class="btn btn-primary">Edit</a>
        <form action="{{ route('absensi_sholat.destroy', $absensiSholat->id) }}" method="post" class="d-inline">
            @csrf
            @method('delete')
            <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus absensi sholat ini?')">Hapus</button>
        </form>
    </div>
@endsection

@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Edit Absensi Sholat</h2>
        <form method="post" action="{{ route('absensi_sholat.update', $absensiSholat->id) }}">
            @csrf
            @method('put')
            <div class="form-group">
                <label for="waktu">Waktu</label>
                <input type="datetime-local" name="waktu" class="form-control" id="waktu" value="{{ $absensiSholat->waktu }}">
            </div>
            <div class="form-group">
                <label for="tgl">Tanggal</label>
                <input type="date" name="tgl" class="form-control" id="tgl" value="{{ $absensiSholat->tgl }}">
            </div>
            <div class="form-group">
                <label for="id_jadwal_sholat">Jadwal Sholat</label>
                <input type="number" name="id_jadwal_sholat" class="form-control" id="id_jadwal_sholat" value="{{ $absensiSholat->id_jadwal_sholat }}">
            </div>
            <div class="form-group">
                <label for="id_devices">Device</label>
                <input type="number" name="id_devices" class="form-control" id="id_devices" value="{{ $absensiSholat->id_devices }}">
            </div>
            <div class="form-group">
                <label for="nis_santri">NIS Santri</label>
                <input type="text" name="nis_santri" class="form-control" id="nis_santri" value="{{ $absensiSholat->nis_santri }}">
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
@endsection

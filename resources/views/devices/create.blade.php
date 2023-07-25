@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Tambah Device</h2>
        <form method="post" action="{{ route('devices.store') }}">
            @csrf
            <div class="form-group">
                <label for="nama">Nama</label>
                <input type="text" name="nama" class="form-control" id="nama" placeholder="Nama">
            </div>
            <div class="form-group">
                <label for="no_sn">Nomor Serial</label>
                <input type="text" name="no_sn" class="form-control" id="no_sn" placeholder="Nomor Serial">
            </div>
            <div class="form-group">
                <label for="lokasi">Lokasi</label>
                <input type="text" name="lokasi" class="form-control" id="lokasi" placeholder="Lokasi">
            </div>
            <div class="form-group">
                <label for="online">Online</label>
                <input type="text" name="online" class="form-control" id="online" placeholder="Online">
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
    </div>
@endsection

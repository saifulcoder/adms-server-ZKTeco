@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Edit Device</h2>
        <form method="post" action="{{ route('devices.update', $device->id) }}">
            @csrf
            @method('put')
            <div class="form-group">
                <label for="nama">Nama</label>
                <input type="text" name="nama" class="form-control" id="nama" value="{{ $device->nama }}">
            </div>
            <div class="form-group">
                <label for="no_sn">Nomor Serial</label>
                <input type="text" name="no_sn" class="form-control" id="no_sn" value="{{ $device->no_sn }}">
            </div>
            <div class="form-group">
                <label for="lokasi">Lokasi</label>
                <input type="text" name="lokasi" class="form-control" id="lokasi" value="{{ $device->lokasi }}">
            </div>
            <div class="form-group">
                <label for="online">Online</label>
                <input type="text" name="online" class="form-control" id="online" value="{{ $device->online }}">
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
@endsection

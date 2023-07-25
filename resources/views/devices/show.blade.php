@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Detail Device</h2>
        <p>Nama: {{ $device->nama }}</p>
        <p>Nomor Serial: {{ $device->no_sn }}</p>
        <p>Lokasi: {{ $device->lokasi }}</p>
        <p>Online: {{ $device->online }}</p>
        <a href="{{ route('devices.edit', $device->id) }}" class="btn btn-primary">Edit</a>
        <form action="{{ route('devices.destroy', $device->id) }}" method="post" class="d-inline">
            @csrf
            @method('delete')
            <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus device ini?')">Hapus</button>
        </form>
    </div>
@endsection

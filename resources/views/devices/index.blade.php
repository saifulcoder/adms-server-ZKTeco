@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Device</h2>
        {{-- <a href="{{ route('devices.create') }}" class="btn btn-primary mb-3">Tambah Device</a> --}}
        <table class="table">
            <thead>
                <tr>
                    {{-- <th scope="col">Nama</th> --}}
                    <th scope="col">Serial Number</th>
                    {{-- <th scope="col">Lokasi</th> --}}
                    <th scope="col">Online</th>
                    {{-- <th scope="col">Aksi</th> --}}
                </tr>
            </thead>
            <tbody>
                @foreach($devices as $device)
                <tr>
                    {{-- <td>{{ $device->nama }}</td> --}}
                    <td>{{ $device->no_sn }}</td>
                    {{-- <td>{{ $device->lokasi }}</td> --}}
                    <td>{{ $device->online }}</td>
                    {{-- <td>
                        <a href="{{ route('devices.show', $device->id) }}" class="btn btn-info btn-sm">Detail</a>
                        <a href="{{ route('devices.edit', $device->id) }}" class="btn btn-primary btn-sm">Edit</a>
                        <form action="{{ route('devices.destroy', $device->id) }}" method="post" class="d-inline">
                            @csrf
                            @method('delete')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus device ini?')">Hapus</button>
                        </form>
                    </td> --}}
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

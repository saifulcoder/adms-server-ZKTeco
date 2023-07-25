@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Daftar Absensi Sholat</h2>
        {{-- <a href="{{ route('absensi_sholat.create') }}" class="btn btn-primary mb-3">Tambah Absensi Sholat</a> --}}
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">NIS Santri</th>
                    <th scope="col">Waktu</th>
                    <th scope="col">Tanggal</th>
                    <th scope="col">Jadwal Sholat</th>
                    <th scope="col">Device</th>
                    {{-- <th scope="col">Aksi</th> --}}
                </tr>
            </thead>
            <tbody>
                @foreach($absensiSholat as $absensi)
                <tr>
                    <td>{{ $absensi->nis_santri }}</td>
                    <td>{{ $absensi->waktu }}</td>
                    <td>{{ $absensi->tgl }}</td>
                    <td>{{ $absensi->id_jadwal_sholat }}</td>
                    <td>{{ $absensi->id_devices }}</td>
                    {{-- <td>
                        <a href="{{ route('absensi_sholat.show', $absensi->id) }}" class="btn btn-info btn-sm">Detail</a>
                        <a href="{{ route('absensi_sholat.edit', $absensi->id) }}" class="btn btn-primary btn-sm">Edit</a>
                        <form action="{{ route('absensi_sholat.destroy', $absensi->id) }}" method="post" class="d-inline">
                            @csrf
                            @method('delete')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus absensi sholat ini?')">Hapus</button>
                        </form>
                    </td> --}}
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

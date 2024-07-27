@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>{{ $lable }}</h2>
        {{-- <a href="{{ route('devices.create') }}" class="btn btn-primary mb-3">Tambah Device</a> --}}
        <table class="table table-bordered data-table" id="devices">
            <thead>
                <tr>
                    {{-- <th>No</th> --}}
                    <th>Serial Number</th>
                    <th>Online</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($log as $d)
                    <tr>
                        {{-- <td>{{ $d->id }}</td> --}}
                        <td>{{ $d->no_sn }}</td>
                        <td>{{ $d->online }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
@endsection

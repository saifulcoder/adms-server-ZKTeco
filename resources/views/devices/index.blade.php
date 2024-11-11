@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>{{ $title }}</h2>
        <a href="{{ route('devices.create') }}" class="btn btn-primary mb-3">Create Device</a>
        <table class="table table-bordered data-table" id="devices">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ubicacion</th>
                    <th>Online</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($log as $d)
                    <tr>
                        <td>{{ $d->idreloj }}</td>
                        <td>{{ $d->name }}</td>
                        <td>{{ $d->online }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
@endsection

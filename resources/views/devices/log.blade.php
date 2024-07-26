@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>{{ $lable }}</h2>
        {{-- <a href="{{ route('devices.create') }}" class="btn btn-primary mb-3">Tambah Device</a> --}}
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">DATA</th>
                </tr>
            </thead>
            <tbody>
                @foreach($log as $d)
                <tr>
                    <td>{{ $d->id }}</td>
                    <td>{{ $d->data }}</td>
                    
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

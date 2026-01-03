@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>{{ $title }}</h2>
        <table class="table table-bordered data-table w-100" id="devices">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Id Agente</th>
                    <th>Nombre</th>
                    <th>ID Reloj</th>
                    <th class="w-20">Fecha</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($deviceLogs as $d)                
                    <tr>
                        <td>{{ $d->id }}</td>
                        <td class="text-wrap">{{ $d->idagente}}</td>
                        <td>{{ $d->employee?->fullname }}</td>
                        <td>{{ $d->device?->name }}</td>
                        <td>{{ $d->created_at }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
@endsection

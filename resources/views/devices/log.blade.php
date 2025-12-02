@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>{{ $title }}</h2>
        <table class="table table-bordered data-table w-100" id="devices">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Url</th>
                    <th class="w-50">Data</th>
                    <th>ID Reloj</th>
                    <th class="w-20">Fecha</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($deviceLogs as $d)
                    <tr>
                        <td>{{ $d->id }}</td>
                        <td>{{ $d->url }}</td>
                        <td class="text-wrap">{{ $d->data }}</td>
                        <td>{{ $d->idreloj }}</td>
                        <td>{{ $d->created_at }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
@endsection

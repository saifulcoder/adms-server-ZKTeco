@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>{{ $lable }}</h2>
        <table class="table table-bordered data-table" id="devices">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($log as $d)
                    <tr>
                        <td>{{ $d->id }}</td>
                        <td>{{ $d->data }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
@endsection

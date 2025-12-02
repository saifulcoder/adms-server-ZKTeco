@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Employee list</h2>
        <a href="{{ route('agentes.pull') }}" class="btn btn-primary mb-3">Pull Employees</a>

        @if(session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif
        <div class="form-group">
            <label for="oficina">Offices</label>
            <form method="GET" action="{{ route('agentes.index', ['selectedOficina' => $selectedOficina]) }}" id="oficinaForm">
                <select name="selectedOficina" class="form-control" id="selectedOficina">
                    @foreach ($oficinas as $oficina)
                        <option value="{{ $oficina->idoficina }}" 
                            {{ $oficina->idoficina == $selectedOficina ? 'selected' : '' }}>
                            {{ $oficina->ubicacion }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
        <br>
        <table class="table table-bordered data-table" id="employees">
            <thead>
                <tr>
                    <th>ID Empresa</th>
                    <th>ID Oficina</th>
                    <th>ID Agente</th>
                    <th>Shortname</th>
                    <th>Full Name</th>
                    <th>Last update</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($agentes as $agente)
                    <tr>
                        <td>{{ $agente->idempresa }}</td>
                        <td>{{ $agente->idoficina }}</td>
                        <td>{{ $agente->idagente }}</td>
                        <td>{{ $agente->shortname }}</td>
                        <td>{{ $agente->fullname }}</td>
                        <td>{{ $agente->updated_at }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#selectedOficina').change(function() {
                $('#oficinaForm').submit();
            });
        });
    </script>

@endsection
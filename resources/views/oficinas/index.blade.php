@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>{{ $title }}</h2>
        <a href="{{ route('oficinas.create') }}" class="btn btn-primary mb-3">Create Oficina</a>
        <!-- success message -->
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        <!-- error message -->
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
        <table class="table table-bordered data-table" id="oficinas">
            <thead>
                <tr>
                    <th></th>
                    <th>ID</th>
                    <th>ID Empresa</th>
                    <th>ID Oficina</th>
                    <th>Ubicación</th>
                    <th>Public URL</th>
                    <th>IATA Code</th>
                    <th>City Timezone</th>
                    <th>Timezone</th>
                    <th>Última Actualización</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($oficinas as $oficina)
                    <tr>
                        <td><input type="checkbox" name="oficina" value="{{ $oficina->id }}"></td>
                        <td>{{ $oficina->id }}</td>
                        <td>{{ $oficina->idempresa }}</td>
                        <td>{{ $oficina->idoficina }}</td>
                        <td>{{ $oficina->ubicacion }}</td>
                        <td><a href="{{ $oficina->public_url }}" target="_blank">{{ $oficina->public_url }}</a></td>
                        <td>{{ $oficina->iatacode }}</td>
                        <td>{{ $oficina->city_timezone }}</td>
                        <td>{{ $oficina->timezone }}</td>
                        <td>{{ $oficina->updated_at?->diffForHumans() }}</td>
                        <td>
                            <a href="{{ route('oficinas.edit', ['id' => $oficina->id ]) }}" class="btn btn-primary">Edit</a>
                            <a href="{{ route('oficinas.delete', ['id' => $oficina->id ]) }}" class="btn btn-danger delete-btn">Delete</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="d-flex justify-content-center">
            <button class="btn btn-danger" id="delete-all">Delete Selected</button>
        </div>
    </div>

    <!-- Confirm Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Confirm Action</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this oficina?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="cancelModal" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmBtn">Confirm</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let targetUrl = '';

            document.querySelectorAll('.delete-btn').forEach(function (button) {
                button.addEventListener('click', function (event) {
                    event.preventDefault();
                    targetUrl = this.href;
                    $('#confirmModal').modal('show');
                });
            });

            document.getElementById('confirmBtn').addEventListener('click', function () {
                window.location.href = targetUrl;
            });

            document.getElementById('cancelModal').addEventListener('click', function () {
                $('#confirmModal').modal('hide');
            });

            // refresh data-table every 25 seconds
            setInterval(function () {
                window.location.reload();
            }, 25000);
        });
    </script>
@endsection

@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>{{ $title }}</h2>
        <a href="{{ route('devices.create') }}" class="btn btn-primary mb-3">Create Device</a>
        <table class="table table-bordered data-table" id="devices">
            <thead>
                <tr>
                    <th></th>
                    <th>ID</th>
                    <th>Oficina</th>
                    <th>Ubicacion</th>
                    <th>Online</th>
                    <th>Ultima checada</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($log as $d)
                    <tr>
                        <td><input type="checkbox" name="device" value="{{ $d->id }}"></td>
                        <td>{{ $d->idreloj }}</td>
                        <td>{{ $d->oficina->ubicacion ?? '' }}</td>
                        <td>{{ $d->name }}</td>
                        <td class="text-center align-middle">
                            @if ($d->online)
                                @php
                                    $diffInMinutes = $d->online->diffInMinutes(now());
                                @endphp

                                @if ($diffInMinutes < 1)
                                    <i class="fas fa-check-circle text-success"></i>
                                @elseif ($diffInMinutes <= 5)
                                    <i class="fas fa-exclamation-circle text-warning"></i>
                                @else
                                    <i class="fas fa-times-circle text-danger"></i>
                                @endif
                            @else
                                <span style="color: gray;">unknown</span>
                            @endif
                        </td>
                        <td>{{ $d->getLastAttendance() ? $d->getLastAttendance()->created_at->diffForHumans() : 'unknown' }}</td>
                        <td>
                            <a href="{{ route('devices.populate', ['id' => $d->id ]) }}" class="btn btn-info">Update Employees</a>                            
                            <a href="{{ route('devices.edit', ['id' => $d->id ]) }}" class="btn btn-primary">Edit</a>
                            <a href="{{ route('devices.restart', ['id' => $d->id ]) }}" class="btn btn-primary restart-btn">Push Restart</a>                            
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="d-flex justify-content-center">
            <button class="btn btn-danger" id="delete-all">Update Selected</button>
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
                    Are you sure you want to restart this device?
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

            document.querySelectorAll('.restart-btn').forEach(function (button) {
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
        });
    </script>
@endsection

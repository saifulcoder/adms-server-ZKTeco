@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Edit Attendance Record</h2>
        <form method="post" action="{{ route('devices.attendance.update', ['id' => $attendanceRecord->id ]) }}">
            @csrf
            <input type="hidden" name="id" value="{{ $attendanceRecord->id }}">
            <div class="form-group">
                <label for="name">Nombre</label>
                <input type="text" name="name" class="form-control" id="name" readonly value="{{ $attendanceRecord->getEmployee()?->fullname }}">
            </div>
            <div class="form-group">
                <label for="serial_number">Updated at</label>
                <input type="text" class="form-control" readonly value="{{ $attendanceRecord->updated_at }}">
            </div>
            <div class="form-group">
                <label for="serial_number">Timestamp</label>
                <input type="text" name="timestamp" class="form-control" id="timestamp" value="{{ $attendanceRecord->timestamp }}">
            </div>
            <div class="form-group">
                <label for="idreloj">ID Reloj</label>
                <input type="text" name="idreloj" class="form-control" id="idreloj"readonly value="{{ $attendanceRecord->idreloj }}">
            </div>            
            <br/>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
@endsection

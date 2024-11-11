@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Edit Device</h2>
        <form method="post" action="{{ route('devices.update', $device->id) }}">
            @csrf
            @method('put')
            <div class="form-group">
                <label for="name">Nombre</label>
                <input type="text" name="name" class="form-control" id="name" value="{{ $device->name }}">
            </div>
            <div class="form-group">
                <label for="no_sn">Numero de Serie</label>
                <input type="text" name="no_sn" class="form-control" id="no_sn" value="{{ $device->no_sn }}">
            </div>
            <div class="form-group">
                <label for="idreloj">ID Reloj</label>
                <input type="text" name="idreloj" class="form-control" id="idreloj" value="{{ $device->idreloj }}">
            </div>
            <div class="form-group">
                <label for="online">Online</label>
                <input type="text" name="online" class="form-control" id="online" value="{{ $device->online }}">
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
@endsection

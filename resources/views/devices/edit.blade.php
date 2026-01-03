@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Edit Device</h2>
        <form method="post" action="{{ route('devices.update', ['id' => $device->id ]) }}">
            @csrf
            <input type="hidden" name="id" value="{{ $device->id }}">
            <div class="form-group">
                <label for="name">Nombre</label>
                <input type="text" name="name" class="form-control" id="name" value="{{ $device->name }}">
            </div>
            <div class="form-group">
                <label for="serial_number">Numero de Serie</label>
                <input type="text" name="serial_number" class="form-control" id="serial_number" value="{{ $device->serial_number }}">
            </div>
            <div class="form-group">
                <label for="idreloj">ID Reloj</label>
                <input type="text" name="idreloj" class="form-control" id="idreloj" value="{{ $device->idreloj }}">
            </div>
            <div class="form-group">
                <label for="idoficina">Oficina</label>
                <select name="idoficina" class="form-control" id="idoficina">
                    @foreach ($oficinas as $oficina)
                        <option value="{{ $oficina->idoficina }}" @if($device->idoficina == $oficina->idoficina) selected @endif>{{ $oficina->ubicacion }}</option>
                    @endforeach
                </select>
            </div>   
            <div class="form-group">
                <label for="online">Online</label>
                <input type="text" name="online" class="form-control" id="online" value="{{ $device->online }}">
            </div>
            <br/>
            <button type="submit" class="btn btn-primary">Update</button>
            <!-- remove device -->
            <a href="{{ route('devices.delete', ['id' => $device->id ]) }}" class="btn btn-danger">Delete</a>
            <a href="{{ route('devices.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection

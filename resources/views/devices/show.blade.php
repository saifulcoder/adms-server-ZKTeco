@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Dispositivo Biometrico</h2>
        <p>Nombre: {{ $device->name }}</p>
        <p>Numero serie: {{ $device->no_sn }}</p>
        <p>ID Reloj: {{ $device->idreloj }}</p>
        <p>Online: {{ $device->online }}</p>
        <a href="{{ route('devices.edit', $device->id) }}" class="btn btn-primary">Edit</a>
    </div>
@endsection

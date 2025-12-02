@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Create Biometric Recor</h2>
        <form method="post" action="{{ route('devices.store') }}">
            @csrf
            <div class="form-group">
                <label for="name">Location</label>
                <input type="text" name="name" class="form-control" id="name" placeholder="Location">
            </div>
            <div class="form-group">
                <label for="no_sn">Serial Number</label>
                <input type="text" name="no_sn" class="form-control" id="no_sn" placeholder="SN00001">
            </div>
            <div class="form-group">
                <label for="lokasi">ID Reloj</label>
                <input type="text" name="idreloj" class="form-control" id="idreloj" placeholder="ID Reloj">
            </div>
            <div class="form-group">
                <label for="lokasi">IP</label>
                <input type="text" name="ip" class="form-control" id="ip" placeholder="IP">
            </div>

            <button type="submit" class="btn btn-primary">Enviar</button>
        </form>
    </div>
@endsection

@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Edit Oficina</h2>
        <form method="post" action="{{ route('oficinas.update', ['id' => $oficina->id ]) }}">
            @csrf
            <input type="hidden" name="idoficina" value="{{ $oficina->idoficina }}">
            <div class="form-group">
                <label for="idempresa">ID Empresa</label>
                <input type="text" name="idempresa" class="form-control" id="idempresa" value="{{ $oficina->idempresa }}">
            </div>
            <div class="form-group">
                <label for="idoficina">ID Oficina</label>
                <input type="text" name="idoficina" class="form-control" id="idoficina" value="{{ $oficina->idoficina }}">
            </div>
            <div class="form-group">
                <label for="ubicacion">Ubicación</label>
                <input type="text" name="ubicacion" class="form-control" id="ubicacion" value="{{ $oficina->ubicacion }}">
            </div>
            <div class="form-group">
                <label for="public_url">Public URL</label>
                <input type="text" name="public_url" class="form-control" id="public_url" value="{{ $oficina->public_url }}">
            </div>
            <div class="form-group">
                <label for="iatacode">IATA Code</label>
                <input type="text" name="iatacode" class="form-control" id="iatacode" value="{{ $oficina->iatacode }}">
            </div>
            <div class="form-group">
                <label for="city_timezone">City Timezone</label>
                <input type="text" name="city_timezone" class="form-control" id="city_timezone" value="{{ $oficina->city_timezone }}">
            </div>
            <div class="form-group">
                <label for="timezone">Timezone</label>
                <input type="text" name="timezone" class="form-control" id="timezone" value="{{ $oficina->timezone }}">
            </div>
            <br/>
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('oficinas.delete', ['id' => $oficina->id ]) }}" class="btn btn-danger">Delete</a>
            <a href="{{ route('devices.oficinas') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection

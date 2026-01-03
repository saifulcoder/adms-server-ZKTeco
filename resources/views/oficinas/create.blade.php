@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Create Oficina</h2>
        <form method="post" action="{{ route('oficinas.store') }}">
            @csrf
            <div class="form-group">
                <label for="idempresa">ID Empresa</label>
                <input type="text" name="idempresa" class="form-control" id="idempresa" value="{{ old('idempresa') }}">
            </div>
            <div class="form-group">
                <label for="ubicacion">Ubicación</label>
                <input type="text" name="ubicacion" class="form-control" id="ubicacion" value="{{ old('ubicacion') }}">
            </div>
            <div class="form-group">
                <label for="public_url">Public URL</label>
                <input type="text" name="public_url" class="form-control" id="public_url" value="{{ old('public_url') }}">
            </div>
            <div class="form-group">
                <label for="iatacode">IATA Code</label>
                <input type="text" name="iatacode" class="form-control" id="iatacode" value="{{ old('iatacode') }}">
            </div>
            <div class="form-group">
                <label for="city_timezone">City Timezone</label>
                <input type="text" name="city_timezone" class="form-control" id="city_timezone" value="{{ old('city_timezone') }}">
            </div>
            <div class="form-group">
                <label for="timezone">Timezone</label>
                <input type="text" name="timezone" class="form-control" id="timezone" value="{{ old('timezone') }}">
            </div>
            <br/>
            <button type="submit" class="btn btn-primary">Create</button>
            <a href="{{ route('oficinas.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection

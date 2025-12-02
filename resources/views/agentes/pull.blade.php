@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Pull employees from Office</h2>
        <form method="post" action="{{ route('agentes.runpull') }}">
            @csrf
            <div class="form-group">
                <label for="oficina">Offices</label>
                <select name="oficina" class="form-control" id="oficina">
                    @foreach ($oficinas as $oficina)
                        <option value="{{ $oficina->idoficina }}">{{ $oficina->ubicacion }}</option>
                    @endforeach
                </select>
            </div>            
            <button type="submit" class="btn btn-primary">Run Request</button>
        </form>
    </div>
@endsection

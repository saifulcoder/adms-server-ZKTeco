@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Checadas</h2>

    @if(session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif
    <div class="form-group">
        <label for="oficina">Offices</label>
        <form method="GET" action="{{ route('devices.attendance') }}" id="oficinaForm">
            <input type="hidden" name="page" value="{{ request('page', 1) }}">
            <select name="selectedOficina" class="form-control" id="selectedOficina">
                @foreach ($oficinas as $oficina)
                    <option value="{{ $oficina->idoficina }}" 
                        {{ $oficina->idoficina == $selectedOficina ? 'selected' : '' }}>
                        {{ $oficina->ubicacion }}
                    </option>
                @endforeach
            </select>
            <input type="checkbox" name="desfasados" id="desfasados" 
                {{ request('desfasados') ? 'checked' : '' }}>
            <label for="desfasados">Diff>20min</label><br>
            <button type="submit" class="btn btn-primary mt-2">Filter</button>
        </form>
    </div>
    <br>

    <div class="table-responsive">
        <table class="table table-bordered data-table">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Device</th>
                    <th>Employee ID</th>
                    <th>Employee</th>
                    <th>Timestamp</th>
                    <th>Updated at</th>
                    <th>Diff</th>
                    <th>Checada Uniqueid</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendances as $attendance)
                    <tr>
                        <td>{{ $attendance->id }}</td>
                        <td>{{ $attendance->device ? $attendance->device->name : 'No registrado' }}</td>
                        <td>{{ $attendance->employee_id }}</td>
                        <td>{!! $attendance->getEmployee()?->fullname ?? '<em>Unknown</em>' !!}</td>
                        <td>{{ $attendance->timestamp }}</td>
                        <td>{{ $attendance->updated_at }}</td>
                        <td class="{{ $attendance->updated_at->diffInMinutes($attendance->timestamp) > 3 ? 'text-danger' : '' }}">
                            {{ $attendance->updated_at->diffForHumans($attendance->timestamp) }}
                        </td>
                        <td>{{ $attendance->response_uniqueid }}</td>
                        <td>
                            <a href="{{ route('devices.attendance.edit', $attendance->id) }}" class="btn btn-primary">Edit</a>
                        </td>
                        <td>
                            <button onclick="fixAttendance({{ $attendance->id }}, this)" class="btn btn-primary">Fix</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="d-felx justify-content-center">
        {{ $attendances->links() }}  
    </div>
</div>

<style>
    .floating-messages {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 2000;
        max-width: 360px;
    }
    .floating-message {
        background: #ffffff;
        border-left: 4px solid #0d6efd;
        border-radius: 6px;
        box-shadow: 0 6px 18px rgba(0,0,0,0.12);
        padding: 10px 12px;
        margin-bottom: 10px;
        font-size: 14px;
        opacity: 0;
        transform: translateY(-6px);
        transition: opacity .2s ease, transform .2s ease;
    }
    .floating-message.show { opacity: 1; transform: translateY(0); }
    .floating-message.success { border-left-color: #28a745; }
    .floating-message.error { border-left-color: #dc3545; }
    .floating-message .title { font-weight: 600; margin-right: 6px; }
</style>

<div id="floating-messages" class="floating-messages"></div>

<script>
function showFloatingMessage(message, isSuccess) {
    const container = document.getElementById('floating-messages');
    if (!container) return;

    const el = document.createElement('div');
    el.className = 'floating-message ' + (isSuccess ? 'success' : 'error');
    el.innerHTML = '<span class="title">' + (isSuccess ? 'Éxito' : 'Error') + ':</span>' +
                   '<span>' + (message || (isSuccess ? 'Operación completada' : 'Ocurrió un error')) + '</span>';

    container.appendChild(el);
    // Force reflow to play transition
    void el.offsetWidth;
    el.classList.add('show');

    setTimeout(() => {
        el.classList.remove('show');
        setTimeout(() => el.remove(), 200);
    }, 3500);
}

async function fixAttendance(id, btn) {
    const url = '{{ url("devices/retrieve/attendance/fix") }}/' + id;
    const originalText = btn ? btn.textContent : '';
    if (btn) {
        btn.disabled = true;
        btn.textContent = '...';
    }

    try {
        const res = await fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        });

        const data = await res.json().catch(() => ({}));
        const ok = res.ok && data && typeof data.success !== 'undefined' ? data.success : res.ok;
        const msg = (data && data.message) ? data.message : (ok ? 'Registro de asistencia corregido correctamente' : 'No se pudo procesar la corrección');
        showFloatingMessage(msg, !!ok);
    } catch (e) {
        showFloatingMessage('Error de red al procesar la corrección', false);
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.textContent = originalText || 'Fix';
        }
    }
}
</script>
@endsection

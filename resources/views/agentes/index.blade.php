@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>{{ __('agentes.title') }}</h2>
        <div class="mb-3 d-flex gap-2">
            <a href="{{ route('agentes.pull') }}" class="btn btn-primary">{{ __('agentes.pull_employees') }}</a>
            @if($selectedOficina)
                <form method="POST" action="{{ route('agentes.runpurge') }}" class="d-inline"
                      onsubmit="return confirm('{{ __('agentes.purge_confirm') }}');">
                    @csrf
                    <input type="hidden" name="oficina" value="{{ $selectedOficina }}">
                    <input type="hidden" name="idempresa" value="{{ $selectedEmpresa }}">
                    <button type="submit" class="btn btn-outline-danger">{{ __('agentes.purge_removed') }}</button>
                </form>
            @endif
        </div>

        @if(session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if(session('pull_result'))
            @php $pullResult = session('pull_result'); @endphp
            <div class="modal fade" id="pullResultModal" tabindex="-1" aria-labelledby="pullResultModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header {{ !empty($pullResult['failed']) ? 'bg-danger text-white' : 'bg-success text-white' }}">
                            <h5 class="modal-title" id="pullResultModalLabel">
                                {{ !empty($pullResult['failed']) ? __('agentes.pull_failed') : __('agentes.pull_complete') }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            @if(!empty($pullResult['failed']))
                                <p class="mb-0">{{ $pullResult['message'] ?? __('agentes.pull_failed') }}</p>
                            @else
                                @if(!empty($pullResult['oficina']))
                                    <p>{{ __('devices.oficina') }}: <strong>{{ $pullResult['oficina'] }}</strong></p>
                                @endif
                                <ul class="mb-0">
                                    <li>{{ __('agentes.pulled') }}: <strong>{{ $pullResult['pulled'] ?? 0 }}</strong></li>
                                    <li>{{ __('agentes.new_agents') }}: <strong>{{ $pullResult['created'] ?? 0 }}</strong></li>
                                    <li>{{ __('agentes.updated_agents') }}: <strong>{{ $pullResult['updated'] ?? 0 }}</strong></li>
                                    <li>{{ __('agentes.restored_agents') }}: <strong>{{ $pullResult['restored'] ?? 0 }}</strong></li>
                                    <li>{{ __('agentes.removed_agents') }}: <strong>{{ $pullResult['removed'] ?? 0 }}</strong></li>
                                    <li>{{ __('agentes.commands_queued') }}: <strong>{{ $pullResult['commands'] ?? 0 }}</strong></li>
                                </ul>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('agentes.close') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if(session('purge_result'))
            @php $purgeResult = session('purge_result'); @endphp
            <div class="modal fade" id="purgeResultModal" tabindex="-1" aria-labelledby="purgeResultModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header {{ !empty($purgeResult['failed']) ? 'bg-danger text-white' : 'bg-success text-white' }}">
                            <h5 class="modal-title" id="purgeResultModalLabel">
                                {{ !empty($purgeResult['failed']) ? __('agentes.purge_failed') : __('agentes.purge_complete') }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            @if(!empty($purgeResult['failed']))
                                <p class="mb-0">{{ $purgeResult['message'] ?? __('agentes.purge_failed') }}</p>
                            @else
                                @if(!empty($purgeResult['oficina']))
                                    <p>{{ __('devices.oficina') }}: <strong>{{ $purgeResult['oficina'] }}</strong></p>
                                @endif
                                <ul class="mb-0">
                                    <li>{{ __('agentes.removed_agents') }}: <strong>{{ $purgeResult['employees'] ?? 0 }}</strong></li>
                                    <li>{{ __('agentes.device_deletes_queued') }}: <strong>{{ $purgeResult['commands'] ?? 0 }}</strong></li>
                                </ul>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('agentes.close') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="form-group">
            <label for="oficina">{{ __('navigation.oficinas') }}</label>
            <form method="GET" action="{{ route('agentes.index') }}" id="oficinaForm">
                <select name="oficina_id" class="form-control" id="selectedOficina">
                    <option value="" {{ $selectedOficinaId ? '' : 'selected' }}>--</option>
                    @foreach ($oficinas as $oficina)
                        <option value="{{ $oficina->id }}"
                            {{ $oficina->id == $selectedOficinaId ? 'selected' : '' }}>
                            {{ $oficina->ubicacion }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
        <br>
        <table class="table table-bordered data-table" id="employees">
            <thead>
                <tr>
                    <th>{{ __('agentes.id_empresa') }}</th>
                    <th>{{ __('agentes.id_oficina') }}</th>
                    <th>{{ __('agentes.id_agente') }}</th>
                    <th>{{ __('agentes.shortname') }}</th>
                    <th>{{ __('agentes.fullname') }}</th>
                    <th>{{ __('agentes.fingerprints') }}</th>
                    <th>{{ __('agentes.face') }}</th>
                    <th>{{ __('agentes.last_update') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($agentes as $agente)
                    <tr>
                        <td>{{ $agente->idempresa }}</td>
                        <td>{{ $agente->idoficina }}</td>
                        <td>{{ $agente->idagente }}</td>
                        <td>{{ $agente->shortname }}</td>
                        <td>{{ $agente->fullname }}</td>
                        @php
                            $key = "{$agente->idempresa}|{$agente->idoficina}|{$agente->idagente}";
                            $fingers = $fingerCounts[$key] ?? 0;
                            $faces = $faceCounts[$key] ?? 0;
                        @endphp
                        <td data-order="{{ $fingers }}">
                            @if($fingers > 0)
                                <span class="badge bg-success">{{ __('agentes.biometrics_pulled', ['count' => $fingers]) }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('agentes.biometrics_none') }}</span>
                            @endif
                        </td>
                        <td data-order="{{ $faces }}">
                            @if($faces > 0)
                                <span class="badge bg-success">{{ __('agentes.biometrics_pulled', ['count' => $faces]) }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('agentes.biometrics_none') }}</span>
                            @endif
                        </td>
                        <td>{{ $agente->updated_at }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            @if(session('pull_result'))
                var pullResultModalEl = document.getElementById('pullResultModal');
                if (pullResultModalEl) {
                    var pullResultModal = new bootstrap.Modal(pullResultModalEl);
                    pullResultModal.show();
                }
            @endif

            @if(session('purge_result'))
                var purgeResultModalEl = document.getElementById('purgeResultModal');
                if (purgeResultModalEl) {
                    var purgeResultModal = new bootstrap.Modal(purgeResultModalEl);
                    purgeResultModal.show();
                }
            @endif

            $('#selectedOficina').change(function() {
                $('#oficinaForm').submit();
            });
        });
    </script>

@endsection
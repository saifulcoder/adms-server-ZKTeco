<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Oficina;
use App\Models\Agente;
use App\Models\FaceTemplate;
use App\Models\FingerprintTemplate;
use App\Services\SyncStationEmployeesService;
use App\Services\RemoveStationEmployeesService;
use Illuminate\Support\Facades\Log;

class AgentesController extends Controller
{
    public function index(Request $request)
    {
        $oficinas = Oficina::all();

        // The oficinas.id is the only unique key: idoficina can repeat across empresas.
        $selected = null;
        if ($request->filled('oficina_id')) {
            $selected = $oficinas->firstWhere('id', (int) $request->query('oficina_id'));
        } elseif ($request->filled('selectedOficina')) {
            $selected = $oficinas->first(function ($o) use ($request) {
                return $o->idoficina == $request->query('selectedOficina')
                    && (!$request->filled('idempresa') || $o->idempresa == $request->query('idempresa'));
            });
        }

        if ($selected) {
            $agentes = Agente::where('idoficina', $selected->idoficina)
                ->where('idempresa', $selected->idempresa)
                ->get()->sortBy('idagente');
        } else {
            $agentes = Agente::all()->sortBy('idagente');
        }

        $selectedOficina = $selected ? $selected->idoficina : null;
        $selectedEmpresa = $selected ? $selected->idempresa : null;
        $selectedOficinaId = $selected ? $selected->id : null;

        $fingerCounts = $this->templateCounts(FingerprintTemplate::class, $selected);
        $faceCounts = $this->templateCounts(FaceTemplate::class, $selected);

        return view('agentes.index', compact('agentes', 'oficinas', 'selectedOficina', 'selectedEmpresa', 'selectedOficinaId', 'fingerCounts', 'faceCounts'));
    }

    /**
     * Valid biometric templates already pulled, keyed empresa|oficina|pin (pin
     * on the terminal == agentes.idagente). Counts distinct slots so the same
     * finger/face held by several terminals is not counted twice.
     *
     * @param class-string<FingerprintTemplate|FaceTemplate> $model
     */
    protected function templateCounts(string $model, ?Oficina $office)
    {
        $query = $model::valid()
            ->selectRaw('idempresa, idoficina, pin, COUNT(DISTINCT fid) as slots')
            ->groupBy('idempresa', 'idoficina', 'pin');

        if ($office) {
            $query->forOffice($office->idempresa, $office->idoficina);
        }

        return $query->get()->mapWithKeys(fn ($row) => [
            "{$row->idempresa}|{$row->idoficina}|{$row->pin}" => (int) $row->slots,
        ]);
    }

    public function pullAgentes(Request $request)
    {
        $oficinas = Oficina::all();
        return view('agentes.pull', compact('oficinas'));
    }

    public function runPullAgentes(Request $request, SyncStationEmployeesService $service)
    {
		$idoficina = $request->input('oficina');
		$idempresa = $request->input('idempresa');
		$oficinaQuery = Oficina::where('idoficina', $idoficina);
		if (!empty($idempresa)) {
			$oficinaQuery->where('idempresa', $idempresa);
		}
		$oficina = $oficinaQuery->first();

		if (!$oficina) {
			Log::warning('runPullAgentes: office not found', [
				'idoficina' => $idoficina,
				'idempresa' => $idempresa,
			]);

			$request->session()->flash('pull_result', [
				'failed' => true,
				'message' => 'Office not found.',
			]);

			return redirect()->route('agentes.index');
		}

		Log::debug('runPullAgentes: manual pull triggered', [
			'idoficina' => $oficina->idoficina,
			'idempresa' => $oficina->idempresa,
			'triggered_by' => optional($request->user())->email ?? optional($request->user())->id,
		]);

		// Run synchronously so we can report accurate counts back to the user
		// immediately, instead of a fire-and-forget queued job whose result
		// nobody sees.
		$result = $service->syncOffice($oficina);

		Log::info('runPullAgentes: manual pull finished', array_merge($result, [
			'idoficina' => $oficina->idoficina,
			'idempresa' => $oficina->idempresa,
		]));

		$request->session()->flash('pull_result', array_merge($result, [
			'oficina' => $oficina->ubicacion,
		]));

        return redirect()->route('agentes.index');
    }

    /**
     * Deferred device-removal step: push DATA DELETE USERINFO commands to the
     * office's devices for every agent that was marked removed locally but not
     * yet queued for device removal.
     */
    public function runPurgeRemoved(Request $request, RemoveStationEmployeesService $service)
    {
        $idoficina = $request->input('oficina');
        $idempresa = $request->input('idempresa');

        $oficinaQuery = Oficina::where('idoficina', $idoficina);
        if (!empty($idempresa)) {
            $oficinaQuery->where('idempresa', $idempresa);
        }
        $oficina = $oficinaQuery->first();

        if (!$oficina) {
            Log::warning('runPurgeRemoved: office not found', [
                'idoficina' => $idoficina,
                'idempresa' => $idempresa,
            ]);

            $request->session()->flash('purge_result', [
                'failed' => true,
                'message' => 'Office not found.',
            ]);

            return redirect()->route('agentes.index');
        }

        Log::debug('runPurgeRemoved: manual purge triggered', [
            'idoficina' => $oficina->idoficina,
            'idempresa' => $oficina->idempresa,
            'triggered_by' => optional($request->user())->email ?? optional($request->user())->id,
        ]);

        $result = $service->purgeOffice($oficina);

        Log::info('runPurgeRemoved: manual purge finished', array_merge($result, [
            'idoficina' => $oficina->idoficina,
            'idempresa' => $oficina->idempresa,
        ]));

        $request->session()->flash('purge_result', array_merge($result, [
            'oficina' => $oficina->ubicacion,
        ]));

        return redirect()->route('agentes.index', ['oficina_id' => $oficina->id]);
    }
}

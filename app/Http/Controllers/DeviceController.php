<?php

namespace App\Http\Controllers;

use App\Models\DeviceLog;
use Log;
use Yajra\DataTables\Facades\Datatables;
use App\Services\CommandIdService;
use Illuminate\Http\Request;
use App\Models\Agente;
use App\Models\Device;
use App\Models\Oficina;
use App\Models\Attendance;
use App\Models\Command;
use App\Models\FingerLog;
use DB;

class DeviceController extends Controller
{
    // Menampilkan daftar device
    public function index(Request $request)
    {
        $data['title'] = "Biometric Devices";
        $data['log'] = Device::all();
        return view('devices.index',$data);
    }

    public function DeviceLog(Request $request)
    {
        $title = "Devices Log";
        $deviceLogs = DeviceLog::orderBy('id', 'DESC')->paginate(40);
        return view('devices.log', compact('deviceLogs', 'title'));
    }
    
    public function FingerLog(Request $request)
    {
        $title = "Finger Log";
        $deviceLogs = FingerLog::orderBy('id', 'DESC')->paginate(40);
        return view('devices.log', compact('deviceLogs', 'title'));
    }

    public function fingerprints(Request $request){
        $title = "Fingerprints captured";
        $deviceLogs = FingerLog::where('data', 'like', '%FP PIN%')
            ->orderBy('updated_at', 'ASC')
            ->paginate(40)
            ->through(function ($log) {
                preg_match('/FP PIN=(\d+)/', $log->data, $matches);
                $log->idagente = $matches[1] ?? null; // Extracted FP PIN value
                $data = json_decode($log->url);
                $log->employee = Agente::where('idagente', $log->idagente)->first();
                $log->device = Device::where('serial_number', $data->SN)->first();
                return $log;
            });
        return view('devices.fingerprints', compact('deviceLogs','title'));
    }

    public function Attendance(Request $request) {
        $selectedOficina = $request->query('selectedOficina');
        if ($selectedOficina) {
            $attendances = Attendance::where('idoficina', $selectedOficina)
                ->orderBy('timestamp', 'DESC')
                ->paginate(40);
        } else {
            $attendances = Attendance::orderBy('timestamp', 'DESC')
                ->paginate(40);
            $paginator = $query->paginate(100, ['*'], 'page', $page)
                    ->appends(request()->except('page'));
        }
        $oficinas = Oficina::all();
        return view('devices.attendance', compact('attendances', 'oficinas', 'selectedOficina'));
        
    }

    public function create()
    {
        return view('devices.create');
    }

    public function store(Request $request)
    {
        $device = new Device();
        $device->name = $request->input('name');
        $device->serial_number = $request->input('no_sn');
        $device->idreloj = $request->input('idreloj');
        $device->save();

         return redirect()->route('devices.index')->with('success', 'Biometrico actualizado correctamente');
    }

    public function show($id)
    {
         $device = Device::find($id);
         return view('devices.show', compact('device'));
    }

    public function edit($id)
    {
        $device = Device::find($id);
        $oficinas = Oficina::all();
        return view('devices.edit', compact('device', 'oficinas'));
    }

    public function update(Request $request, $id)
    {
        $device = Device::find($id);
        $oficina = Oficina::where('idoficina', $request->input('idoficina'))->first();

        if (!$oficina) {
            return redirect()->route('devices.index')->with('error', 'Oficina no encontrada');
        }
        $device->name = $request->input('name');
        $device->serial_number = $request->input('serial_number');
        $device->idreloj = $request->input('idreloj') ?? '999999';
        $device->idoficina = $oficina->idoficina;
        $device->idempresa = $oficina->idempresa;
        $device->save();
      return redirect()->route('devices.index')->with('success', 'Biométrico actualizado correctamente');
    }

    public function restart(Request $request, $id)
    {
        Log::info('Restart', ['id' => $id]);
        $device = Device::find($id);
        try {
            $cmdIdService = resolve(CommandIdService::class); 
            $nextCmdId = $cmdIdService->getNextCmdId();

            $device->commands()->create([
                'device_id' => $device->id,
                'command' => $nextCmdId,
                'data' => "C:{$nextCmdId}:CONTROL DEVICE 03000000",
                'executed_at' => null
            ]);
            return redirect()->route('devices.index')->with('success', 'Biométrico reiniciado correctamente');
        } catch (\Exception $e) {
            return redirect()->route('devices.index')->with('error', 'Error al reiniciar biométrico');
        }
    }

    public function Populate(Request $request, $id)
    {
        Log::info('Populate', ['id' => $id]);
        $device = Device::find($id);
        try {
            $device->populate();
            return redirect()->route('devices.index')->with('success', 'Biométrico actualizado correctamente');
        } catch (\Exception $e) {
            return redirect()->route('devices.index')->with('error', 'Error al actualizar biométrico');
        }
    }
}

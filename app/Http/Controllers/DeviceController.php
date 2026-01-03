<?php

namespace App\Http\Controllers;

use App\Models\DeviceLog;
use Illuminate\Pagination\LengthAwarePaginator;
use Log;
use Yajra\DataTables\Facades\Datatables;
use App\Services\CommandIdService;
use App\Services\UpdateChecadaService;
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
    const EXCLUDED_EMPLOYEES = [
        '300101', // Exclude employee 300101 admin
    ];

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

    public function deleteDevice(Request $request)
    {
        $device = Device::find($request->input('id'));
        if ($device) {
            // check for pending commands and delete them
            $pendingCommands = Command::where('device_id', $device->id)->get();
            foreach ($pendingCommands as $command) {
                $command->delete();
            }
            $device->delete();
            return redirect()->route('devices.index')->with('success', 'Biométrico eliminado correctamente');
        } else {
            return redirect()->route('devices.index')->with('error', 'Biométrico no encontrado');
        }
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

    // get oficinas list
    public function Oficinas(Request $request)
    {
        $oficinas = Oficina::all();
        $title = "Oficinas";
        return  view('oficinas.index', compact('oficinas','title'));
    }

    public function createOficina(Request $request)
    {
        return view('oficinas.create');
    }

    public function storeOficina(Request $request)
    {
        $oficina = new Oficina();
        $oficina->nombre = $request->input('nombre');
        $oficina->idempresa = $request->input('idempresa');
        $oficina->save();

        return redirect()->route('oficinas.index')->with('success', 'Oficina creada correctamente');
    }

    public function editOficina($id)
    {
        $oficina = Oficina::find($id);
        if (!$oficina) {
            return redirect()->route('devices.oficinas')->with('error', 'Oficina no encontrada');
        }
        return view('oficinas.edit', compact('oficina'));
    }

    public function updateOficina(Request $request, $id)
    {
        $oficina = Oficina::find($id);
        if (!$oficina) {
            return redirect()->route('devices.oficinas')->with('error', 'Oficina no encontrada');
        }
        $oficina->ubicacion = $request->input('ubicacion');
        $oficina->idempresa = $request->input('idempresa');
        $oficina->idoficina = $request->input('idoficina');
        // add the missing fields from this list  id | idempresa | idoficina | ubicacion       | public_url                        | iatacode | city_timezone     | timezone
        $oficina->city_timezone = $request->input('city_timezone');
        $oficina->public_url = $request->input('public_url');
        $oficina->iatacode = $request->input('iatacode');
        $oficina->timezone = $request->input('timezone'); 

        $oficina->save();

        return redirect()->route('devices.oficinas')->with('success', 'Oficina actualizada correctamente');
    }


    public function deleteOficina(Request $request)
    {
        $oficina = Oficina::find($request->input('id'));
        if ($oficina) {
            // Check if there are devices associated with this oficina
            $devices = Device::where('idoficina', $oficina->idoficina)->get();
            foreach ($devices as $device) {
                $device->delete();
            }
            $oficina->delete();
            return redirect()->route('devices.oficinas')->with('success', 'Oficina eliminada correctamente');
        } else {
            return redirect()->route('devices.oficinas')->with('error', 'Oficina no encontrada');
        }
    }

    public function Attendance(Request $request) {
        $selectedOficina = $request->query('selectedOficina');
        $page = $request->query('page', 1);
    
        $query = Attendance::query();
    
        if ($selectedOficina) {
            $query->whereIn('sn', function ($q) use ($selectedOficina) {
                $q->select('serial_number')
                  ->from('devices')
                  ->whereNotIn('employee_id', self::EXCLUDED_EMPLOYEES) // Exclude devices with idreloj 999999 or 0
                  ->where('idoficina', $selectedOficina);
            });
        }
    
        $query->orderBy('updated_at', 'DESC'); // <--- Siempre se ordena por updated_at DESC
    
        if ($request->input('desfasados') === 'on') {
            $filtered = $query->get()->filter(function ($attendance) {
                return $attendance->updated_at->diffInMinutes($attendance->timestamp) > 20;
            });
        
            $filtered = $filtered->sortByDesc('updated_at')->values();
        
            $perPage = 100;
            $currentPageItems = $filtered->slice(($page - 1) * $perPage, $perPage)->values();
        
            $paginator = new LengthAwarePaginator(
                $currentPageItems,
                $filtered->count(),
                $perPage,
                $page,
                [
                    'path' => url()->current(),
                    'query' => request()->query(), // 👈 This appends the current query parameters
                ]
            );
        } else {
            $paginator = $query->paginate(100, ['*'], 'page', $page)
                    ->appends(request()->except('page'));
        }
        
        $oficinas = Oficina::all();
    
        return view('devices.attendance', [
            'attendances' => $paginator,
            'oficinas' => $oficinas,
            'selectedOficina' => $selectedOficina,
            'page' => $page,
        ]);
    }
    

    public function devicesActivity(int $id, Request $request) 
{
    $range = $request->get('range', '1d'); // Default to 1 day

    // 1. Determine the start time and interval
    $now = now();
    switch ($range) {
        case '1h':
            $start = $now->copy()->subHour();
            $interval = 'minute';
            break;
        case '6h':
            $start = $now->copy()->subHours(6);
            $interval = 'minute';
            break;
        case '1d':
            $start = $now->copy()->subDay();
            $interval = 'minute';
            break;
        case '7d':
            $start = $now->copy()->subDays(7);
            $interval = 'hour';
            break;
        case '30d':
            $start = $now->copy()->subDays(30);
            $interval = 'day';
            break;
        case '90d':
            $start = $now->copy()->subDays(90);
            $interval = 'day';
            break;
        default:
            $start = $now->copy()->subDay();
            $interval = 'minute';
    }

    // 2. Get the resolution format for groupBy
    $format = [
        'minute' => '%Y-%m-%d %H:%i:00',
        'hour' => '%Y-%m-%d %H:00:00',
        'day' => '%Y-%m-%d 00:00:00',
    ][$interval];

    // 3. Get the serial number
    $serial = Device::where('id', $id)->value('serial_number');
    if (!$serial) {
        abort(404, 'Device not found');
    }

    // 4. Query DB for logs
    $logs = DeviceLog::select(
        DB::raw("DATE_FORMAT(created_at, '$format') as time_slot"),
        DB::raw("COUNT(*) as count")
    )
    ->where('sn', $serial)
    ->where('url', 'like', '%cdata%')
    ->where('created_at', '>=', $start)
    ->groupBy('time_slot')
    ->orderBy('time_slot')
    ->pluck('count', 'time_slot');

    // 5. Generate full time slot range
    $fullData = [];
    $cursor = $start->copy();
    while ($cursor < $now) {
        $key = $cursor->format(str_replace(['%Y', '%m', '%d', '%H', '%i'], ['Y', 'm', 'd', 'H', 'i'], $format));
        $fullData[$key] = $logs[$key] ?? 0;

        // Advance cursor correctly
        if ($interval === 'minute') {
            $cursor->addMinute();
        } elseif ($interval === 'hour') {
            $cursor->addHour();
        } elseif ($interval === 'day') {
            $cursor->addDay();
        }
    }

    return view('devices.activity', [
        'data' => $fullData,
        'range' => $range,
        'id' => $id,
    ]);
}

public function monitor()
{
    try {
        $devices = Device::with(['oficina'])->get();
        
        // Get the last attendance for each device
        foreach ($devices as $device) {
            try {
                $lastAttendance = $device->getLastAttendance();
                if ($lastAttendance && $lastAttendance->timestamp) {
                    $device->last_attendance_time = $lastAttendance->timestamp;
                    $device->last_attendance_human = $lastAttendance->timestamp->format('H:i');
                } else {
                    $device->last_attendance_time = null;
                    $device->last_attendance_human = 'N/A';
                }
                
                // Get current office time and timezone info
                $device->current_office_time = $device->getCurrentOfficeTime();
                $device->office_timezone = $device->oficina ? $device->oficina->timezone : null;
                $device->office_time_display = $device->current_office_time ? $device->current_office_time->format('H:i') : 'N/A';
                
                // Get timezone discrepancy count
                $device->discrepancy_count = $device->getTimezoneDiscrepancyCount();
                
            } catch (\Exception $e) {
                \Log::error("Error processing device {$device->id}: " . $e->getMessage());
                $device->last_attendance_time = null;
                $device->last_attendance_human = 'Error';
                $device->current_office_time = null;
                $device->office_timezone = null;
                $device->office_time_display = 'Error';
                $device->discrepancy_count = 0;
            }
        }

        return view('devices.monitor', compact('devices'));
    } catch (\Exception $e) {
        \Log::error("Error in monitor method: " . $e->getMessage());
        return redirect()->route('devices.index')->with('error', 'Error loading monitor: ' . $e->getMessage());
    }
}


    public function editAttendance(int $id, Request $request) {
        $attendanceRecord = Attendance::find($id);
        return view('attendance.edit', compact('attendanceRecord'));
    }

    public function fixAttendance(int $id, Request $request) {
        $attendanceRecord = Attendance::find($id);

        if (!$attendanceRecord) {
            $message = 'Registro no encontrado';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 404);
            }
            return view('attendance.fix', ['success' => false, 'message' => $message]);
        }

        $data = [
            'uniqueid' => $attendanceRecord->response_uniqueid,
            'timestamp' => $attendanceRecord->updated_at->format('Y-m-d H:i:s'),
            'serial_number' => $attendanceRecord->serial_number,
            'idreloj' => $attendanceRecord->device->idreloj,
            'status1' => $attendanceRecord->status1,
            'status2' => $attendanceRecord->status2,
            'status3' => $attendanceRecord->status3,
            'status4' => $attendanceRecord->status4,
            'status5' => $attendanceRecord->status5,
            'idoficina' => $attendanceRecord->device->oficina->idoficina,
        ];

        // log $data
        Log::info('Fixing attendance record', ['data' => $data]);

        // use the UpdateChecadaService to send the data
        $updateChecada = app()->make(UpdateChecadaService::class);

        $response = (object)$updateChecada->postData($data); // Adjust the endpoint as necessary
        
        // Check for errors
        if (empty($response)) {
            $errorMsg = "Failed to process record ID {$attendanceRecord->id}. No response from API.";
            Log::error($errorMsg);
            $message = 'Error al procesar el registro de asistencia: No respuesta de la API';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message]);
            }
            return view('attendance.fix', ['success' => false, 'message' => $message]);
        }
        if (!$response) {
            $errorMsg = "Failed to process record ID {$attendanceRecord->id}. No response from API.";
            Log::error($errorMsg);
            $message = 'Error al procesar el registro de asistencia';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message]);
            }
            return view('attendance.fix', ['success' => false, 'message' => $message]);
        }
        if (property_exists($response, 'status') && $response->status == 'failed') {
            $errorMsg = "Failed to process record ID {$attendanceRecord->id}. " . $response->message;
            Log::error($errorMsg);
            $message = 'Error al procesar el registro de asistencia: ' . ($response->message ?? 'Estado fallido');
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message]);
            }
            return view('attendance.fix', ['success' => false, 'message' => $message]);
        }
        
        // Success response
        $message = 'Registro de asistencia corregido correctamente';
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }
        return view('attendance.fix', ['success' => true, 'message' => $message]);
    }

    public function updateAttendance(Request $request) {
        $attendanceRecord = Attendance::find($request->input('id'));
        $attendanceRecord->timestamp = $request->input('timestamp');
        $attendanceRecord->save();
        return redirect()->route('devices.attendance')->with('success', 'Registro de asistencia actualizado correctamente');
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

<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\Device;
use Illuminate\Console\Command;
use Carbon\Carbon;

class MonitorDesfases extends Command
{
    protected $signature = 'monitor:desfases {--threshold=5 : Threshold in minutes for detecting desfases}';
    protected $description = 'Monitor attendance records for time desfases between timestamp and created_at';

    public function handle()
    {
        $threshold = (int) $this->option('threshold');
        $this->info("Iniciando monitoreo de desfases con umbral de {$threshold} minutos...");

        $desfasesEncontrados = $this->detectarDesfases($threshold);
        
        if ($desfasesEncontrados->count() > 0) {
            $this->warn("Se encontraron {$desfasesEncontrados->count()} checadas con desfase:");
            
            foreach ($desfasesEncontrados as $attendance) {
                $diffMinutes = $attendance->created_at->diffInMinutes($attendance->timestamp);
                $device = $attendance->device;
                $oficina = $device ? $device->oficina : null;
                
                $this->line("ID: {$attendance->id} | Empleado: {$attendance->employee_id} | Dispositivo: {$attendance->sn}");
                $this->line("Timestamp: {$attendance->timestamp} | Created: {$attendance->created_at}");
                $this->line("Diferencia: {$diffMinutes} minutos | Oficina: " . ($oficina ? $oficina->nombre : 'N/A'));
                $this->line("---");
                
                // Información del desfase encontrado (sin logging para evitar problemas de permisos)
                $this->warn("DESFASE DETECTADO:");
                $this->warn("  - ID Checada: {$attendance->id}");
                $this->warn("  - Empleado: {$attendance->employee_id}");
                $this->warn("  - Dispositivo: {$attendance->sn}");
                $this->warn("  - Oficina: " . ($oficina ? $oficina->nombre : 'N/A'));
                $this->warn("  - Diferencia: {$diffMinutes} minutos");
            }
            
            $this->error("Se detectaron desfases en las checadas. Revisar la salida anterior para más detalles.");
        } else {
            $this->info("No se encontraron desfases en las checadas recientes.");
        }
        
        return Command::SUCCESS;
    }

    protected function detectarDesfases($threshold)
    {
        // Buscar checadas de las últimas 24 horas para detectar desfases
        $fechaInicio = Carbon::now()->subDay();
        
        $attendances = Attendance::with(['device.oficina'])
            ->where('created_at', '>=', $fechaInicio)
            ->get()
            ->filter(function ($attendance) use ($threshold) {
                // Calcular diferencia en minutos entre created_at y timestamp
                $diffMinutes = $attendance->created_at->diffInMinutes($attendance->timestamp);
                
                // Si hay oficina con timezone, usar lógica más precisa
                if ($attendance->device && $attendance->device->oficina && $attendance->device->oficina->timezone) {
                    $officeTimezone = $attendance->device->oficina->timezone;
                    
                    // Convertir ambos timestamps al timezone de la oficina
                    $attendanceTimeInOfficeTz = $attendance->timestamp->setTimezone($officeTimezone);
                    $createdTimeInOfficeTz = $attendance->created_at->setTimezone($officeTimezone);
                    
                    $diffMinutes = $createdTimeInOfficeTz->diffInMinutes($attendanceTimeInOfficeTz);
                }
                
                return $diffMinutes > $threshold;
            });

        return $attendances;
    }
}

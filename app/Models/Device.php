<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Command;
use App\Models\Oficina;
use App\Models\Attendance;
use App\Services\PopulateEmployeesService;

class Device extends Model
{
    use HasFactory;

    protected $table = 'devices';

    protected $fillable = [
        'serial_number',
        'online',
        'idreloj',
        'idempresa',
        'idoficina',
        'modelo',
    ];

    protected $casts = [
        'last_alert_sent_at' => 'datetime',
        'updated_at' => 'datetime',
        'online' => 'datetime',
    ];

    public function oficina()
    {
        return $this->belongsTo(Oficina::class, 'idoficina', 'idoficina');
    }

    public function getLastAttendance()
    {
        return Attendance::where('sn', $this->serial_number)->orderBy('id', 'desc')->first();
    }

        public function hayDesfasesHoy()
    {
        // Get today's attendances for this device
        $checadasHoy = Attendance::where('sn', $this->serial_number)
            ->whereDate('created_at', now()->toDateString())
            ->get();
        
        $hayDesfases = false;
        
        // Check if device has an office with timezone
        if (!$this->oficina || !$this->oficina->timezone) {
            // If no timezone info, use default behavior
            foreach ($checadasHoy as $attendance) {
                if ($attendance->created_at->diffInMinutes($attendance->timestamp) > 20) {
                    $hayDesfases = true;
                    break;
                }
            }
            return $hayDesfases;
        }
        
        // Get current time in office timezone
        $officeTimezone = $this->oficina->timezone;
        $nowInOfficeTz = now()->setTimezone($officeTimezone);
        
        // go through the attendances and check if there are differences between created_at and timestamp for more than 20min
        foreach ($checadasHoy as $attendance) {
            // Convert attendance timestamp to office timezone for proper comparison
            $attendanceTimeInOfficeTz = $attendance->timestamp->setTimezone($officeTimezone);
            
            // Calculate difference in minutes between when the record was created and the actual attendance time
            // Both times are now in the same timezone (office timezone)
            $diffInMinutes = $attendance->created_at->setTimezone($officeTimezone)
                ->diffInMinutes($attendanceTimeInOfficeTz);
            
            if ($diffInMinutes > 20) {
                $hayDesfases = true;
                break;
            }
        }
        
        return $hayDesfases;
    }

    public function commands()
    {
        return $this->hasMany(Command::class);
    }

    public function scopeOnline($query)
    {
        return $query->where('online', true);
    }

    public function pendingCommands()
    {
        return $this->commands()->pending()->get();
    }

    public function populate()
    {
        try {
            $service = new PopulateEmployeesService($this);
            $service->run();
        } catch (\Exception $e) {
            // log the error
            \Log::error($e->getMessage());
        }        
    }
    
    /**
     * Get current time in the office's timezone
     * @return \Carbon\Carbon|null
     */
    public function getCurrentOfficeTime()
    {
        if (!$this->oficina || !$this->oficina->timezone) {
            return null;
        }
        
        return now()->setTimezone($this->oficina->timezone);
    }
    
    /**
     * Convert a datetime to the office's timezone
     * @param \Carbon\Carbon $datetime
     * @return \Carbon\Carbon|null
     */
    public function convertToOfficeTimezone($datetime)
    {
        if (!$this->oficina || !$this->oficina->timezone || !$datetime) {
            return $datetime;
        }
        
        return $datetime->setTimezone($this->oficina->timezone);
    }
    
    /**
     * Get timezone discrepancy count for today
     * @return int
     */
    public function getTimezoneDiscrepancyCount()
    {
        if (!$this->oficina || !$this->oficina->timezone) {
            return 0;
        }
        
        $checadasHoy = Attendance::where('sn', $this->serial_number)
            ->whereDate('created_at', now()->toDateString())
            ->get();
        
        $discrepancyCount = 0;
        
        foreach ($checadasHoy as $attendance) {
            $attendanceTimeInOfficeTz = $attendance->timestamp->setTimezone($this->oficina->timezone);
            $diffInMinutes = $attendance->created_at->setTimezone($this->oficina->timezone)
                ->diffInMinutes($attendanceTimeInOfficeTz);
            
            if ($diffInMinutes > 20) {
                $discrepancyCount++;
            }
        }
        
        return $discrepancyCount;
    }
}

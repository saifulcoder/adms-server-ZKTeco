<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Command;
use App\Models\Oficina;
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
}

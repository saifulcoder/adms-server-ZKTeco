<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Device;
use App\Models\Oficina;
use App\Models\Agente;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendances';

    protected $fillable = [
        'employee_id',
        'timestamp',
        'sn',
        'idreloj',
        'idoficina',
        'status1',
        'status2',
        'status3',
        'status4',
        'status5',
        'response_uniqueid',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'status1' => 'boolean',
        'status2' => 'boolean',
        'status3' => 'boolean',
        'status4' => 'boolean',
        'status5' => 'boolean',
    ];

    // relation between attendance and device by the sn
    public function device()
    {
        return $this->belongsTo(Device::class, 'sn', 'serial_number');
    }

    public function office()
    {
        return $this->belongsTo(Oficina::class, 'idoficina', 'idoficina');
    }  

    public function getEmployee()
    {
        return Agente::where('idagente', $this->employee_id)->first();
    }
}
<?php

namespace App\Services;

use App\Models\Agente;
use App\Models\Device;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PopulateEmployeesService
{
    protected $device;

    public function __construct(Device $device)
    {
        $this->device = $device;
    }

    public function run()
    {
        Log::info('PopulateEmployeesService', ['job' => self::class]);
        
        $employees = Agente::where('idoficina', $this->device->idoficina)->get();
        Log::info('Employees retrieved', ['employees' => $employees]);

        $lastCommand = $this->device->commands()->latest()->first();

        $CmdId = $lastCommand ? $lastCommand->id : 0;

        foreach ($employees as $employee) {
            
            // create a command to populate the employee
            $command = $this->device->commands()->create([
                'command' => $CmdId,
                'status' => 'pending',
                'device_id' => $this->device->id,
                'data' => $this->updateEmployee($employee, $CmdId)
            ]);   
            $CmdId++;
            Log::info('Command created', ['command' => $command]);
        }
    }

    protected function updateEmployee($employee, $CmdId)
    {
        return "C:{$CmdId}:DATA UPDATE USERINFO PIN={$employee->idagente}	Name={$employee->fullname}	Passwd=	Card=	Grp=1	TZ=0000000100000000	Pri=0	Category=0";
    }
}

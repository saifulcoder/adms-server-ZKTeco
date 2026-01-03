<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\GetStationAgentsService;
use Illuminate\Support\Facades\Log;
use App\Models\Agente;

class pullEmployeesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data;

    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(GetStationAgentsService $service)
    {
        Log::info('Job started', ['job' => self::class]);

        $agents = $service->getStationAgents($this->data);
        Log::info('Agents retrieved', ['agents' => $agents]);
        if (!$agents) {
            Log::error('Failed to get agents', ['job' => self::class]);
            return;
        }
        
        // populate the agents table
        foreach ($agents as $agent) {
            
            // check if agent exists
            $agent = Agente::updateOrCreate(
                ['idagente' => $agent['idagente']],
                [
                    'idempresa' => $this->data->idempresa,
                    'idoficina' => $this->data->idoficina,
                    'idagente' => $agent['idagente'],
                    'shortname' => $agent['shortname'],
                    'fullname' => $agent['nombre'] . ' ' . $agent['apellidos']
                ]
            );
        }


        Log::info('Job completed', ['job' => self::class]);

    }
}

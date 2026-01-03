<?php

namespace App\Services;

use App\Models\Oficina;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GetStationAgentsService
{
    protected $baseUrls;

    public function __construct()
    {
        $this->baseUrls = config('services.apis');
        
        if (!$this->baseUrls) {
            throw new \Exception("API configuration for not found.");
        }
    }

    public function getStationAgents(Oficina $oficina)
    {
        Log::info('getStationAgents', ['job' => self::class]);
        $currentAPI = (object)$this->baseUrls[$oficina->idoficina];
        $headers = [
            'Authorization' => $currentAPI->token,
            'Content-Type' => 'multipart/form-data',
            'Accept' => 'application/json',
        ];

        $form = [
            'idempresa' => $oficina->idempresa,
            'idoficina' => $oficina->idoficina,
        ];
        Log::info('getStationAgents form', ['form' => $form]);
        Log::info('url ' . $oficina->public_url() . '/agentes/getstationagents');
        try {
            $response = Http::withHeaders($headers)
                ->withBody(json_encode($form), 'application/json')
                ->post($oficina->public_url() . '/agentes/getstationagents');
        
            return $response->json();
        } catch (\Exception $e) {
            Log::error('getStationAgents error', ['error' => $e->getMessage()]);
            return (object)[
                'status' => 'failed',
                'message' => $e->getMessage()
            ];
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Services\PushChecadaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncronizeAttendance extends Command
{
    protected $signature = 'api:sincronizeAttendance';
    protected $description = 'Synchronize attendance data to the Webroster APIs.';

    protected $apiServices;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Resolve the PushChecadaService with the necessary parameter (apiName)
        $this->apiServices = app()->make(PushChecadaService::class);

        // Process data and call API
        $this->processDataAndCallApi();
    }

    protected function processDataAndCallApi()
    {
        // Execute the query
        $records = $this->fetchData();

        // Loop through the data and call the API for each record
        foreach ($records as $record) {
            if(!$record->device->oficina){
                $this->error("No office found for record ID {$record->id}");
                continue;
            }
            $data = $this->prepareData($record);
            Log::info("Processing record ID {$record->id} with data: ", $data);
            $response = (object)$this->apiServices->postData($data); // Adjust the endpoint as necessary
            try {
                if ($response->status == 'failed') {
                    $this->error("Failed to process record ID {$record->id}. " . $response->message);
                    continue;
                }            
                if($response->status == 'dropped'){
                    $this->error("Failed to process record ID {$record->id}. " . $response->message);
                    continue;
                }
            } catch (\Exception $e) {
                $this->error("Exception occurred while processing record ID {$record->id}: " . $e->getMessage());
                continue;
            }
            
            $this->info("Processed record ID {$record->id}. " . $response->id);       
            $this->updateRecord($record, $response); 
        }
    }

    protected function fetchData()
    {
        return Attendance::where('response_uniqueid', null)->get();
    }

    protected function updateRecord($record, $response)
    {
        $record->response_uniqueid = $response->id;
        $record->save();
    }

    protected function prepareData($record)
    {
        return [
            'idagente' => $record->employee_id,
            'timestamp' => $record->timestamp,
            'serial_number' => $record->serial_number,
            'idreloj' => $record->device->idreloj,
            'status1' => $record->status1,
            'status2' => $record->status2,
            'status3' => $record->status3,
            'status4' => $record->status4,
            'status5' => $record->status5,
            'idoficina' => $record->device->oficina->idoficina,
        ];
    }
}

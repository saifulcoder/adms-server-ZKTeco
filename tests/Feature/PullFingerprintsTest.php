<?php

namespace Tests\Feature;

use App\Models\Agente;
use App\Models\Command;
use App\Models\Device;
use App\Models\FaceTemplate;
use App\Models\FingerprintTemplate;
use App\Models\Oficina;
use App\Services\PullFingerprintsService;
use App\Services\PushFingerprintsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PullFingerprintsTest extends TestCase
{
    use RefreshDatabase;

    private function office(int $idempresa = 1, int $idoficina = 2): Oficina
    {
        return Oficina::create([
            'idempresa' => $idempresa,
            'idoficina' => $idoficina,
            'ubicacion' => 'Cancun',
            'public_url' => 'https://station.test',
            'token' => 'token',
            'iatacode' => 'CUN',
            'timezone' => 'America/Cancun',
        ]);
    }

    private function device(string $sn = 'SN-1', int $idempresa = 1, int $idoficina = 2): Device
    {
        return Device::create([
            'serial_number' => $sn,
            'idempresa' => $idempresa,
            'idoficina' => $idoficina,
            'idreloj' => '1',
        ]);
    }

    /**
     * Post a raw ADMS body the way a terminal does — not form-encoded.
     */
    private function postCdata(string $uri, string $body)
    {
        return $this->call('POST', $uri, [], [], [], ['CONTENT_TYPE' => 'text/plain'], $body);
    }

    public function test_bulk_pull_queues_a_check_command(): void
    {
        $this->office();
        $device = $this->device();

        $queued = app(PullFingerprintsService::class)->bulk($device);

        $this->assertSame(2, $queued); // CHECK + DATA QUERY USERINFO
        $this->assertStringContainsString(
            ':CHECK',
            $device->commands()->where('type', PullFingerprintsService::TYPE_CHECK)->first()->data
        );
    }

    public function test_targeted_pull_queues_one_query_per_finger(): void
    {
        $this->office();
        $device = $this->device();

        $queued = app(PullFingerprintsService::class)->forPin($device, '1234', [0, 1]);

        $this->assertSame(2, $queued);

        $commands = $device->commands()->where('type', PullFingerprintsService::TYPE_QUERY_FP)->get();

        $this->assertStringContainsString("DATA QUERY FINGERTMP PIN=1234\tFingerID=0", $commands[0]->data);
        $this->assertStringContainsString("DATA QUERY FINGERTMP PIN=1234\tFingerID=1", $commands[1]->data);
        $this->assertSame('PIN:1234/FID:0', $commands[0]->reference);
    }

    public function test_an_identical_pending_pull_is_not_queued_twice(): void
    {
        $this->office();
        $device = $this->device();
        $service = app(PullFingerprintsService::class);

        $this->assertSame(1, $service->forPin($device, '1234', [0]));
        $this->assertSame(0, $service->forPin($device, '1234', [0]));
        $this->assertDatabaseCount('device_commands', 1);
    }

    public function test_roster_pull_covers_every_employee_in_the_office(): void
    {
        $this->office();
        $device = $this->device();

        Agente::create(['idempresa' => 1, 'idoficina' => 2, 'idagente' => 11, 'shortname' => 'a', 'fullname' => 'A']);
        Agente::create(['idempresa' => 1, 'idoficina' => 2, 'idagente' => 12, 'shortname' => 'b', 'fullname' => 'B']);
        // Different office — must not be pulled from this device.
        Agente::create(['idempresa' => 1, 'idoficina' => 9, 'idagente' => 13, 'shortname' => 'c', 'fullname' => 'C']);

        $queued = app(PullFingerprintsService::class)->forDevice($device, null, [0]);

        $this->assertSame(2, $queued);
    }

    public function test_a_face_template_uploaded_by_the_terminal_is_stored(): void
    {
        $this->office();
        $device = $this->device();

        $body = "BIODATA Pin=1234\tNo=0\tValid=1\tType=9\tTmp=RkFDRQ==\r\n";

        $this->postCdata('/iclock/cdata?SN=SN-1&table=OPERLOG', $body)->assertOk();
        // Re-sending the same face must update, not duplicate.
        $this->postCdata('/iclock/cdata?SN=SN-1&table=OPERLOG', $body)->assertOk();

        $this->assertSame(1, FaceTemplate::count());
        $face = FaceTemplate::first();
        $this->assertSame('1234', $face->pin);
        $this->assertSame('RkFDRQ==', $face->template);
        $this->assertEquals($device->id, $face->device_id);
        $this->assertEquals('1', $face->idempresa);
        $this->assertEquals('2', $face->idoficina);
        $this->assertSame(0, FingerprintTemplate::count());
    }

    public function test_a_template_uploaded_by_the_terminal_is_stored(): void
    {
        $this->office();
        $device = $this->device();

        $body = "FP PIN=1234\tFID=0\tSize=1404\tValid=1\tTMP=QUJDREVGRw==\r\n";

        $response = $this->postCdata('/iclock/cdata?SN=SN-1&table=OPERLOG', $body);

        $response->assertOk();

        $template = FingerprintTemplate::first();

        $this->assertNotNull($template);
        $this->assertSame('SN-1', $template->sn);
        $this->assertSame('1234', $template->pin);
        $this->assertSame(0, $template->fid);
        $this->assertSame('QUJDREVGRw==', $template->template);
        $this->assertEquals($device->id, $template->device_id);
        $this->assertEquals('1', $template->idempresa);
        // Nobody asked for it, so it reads as an enrolment push.
        $this->assertSame('push', $template->source);
    }

    public function test_a_template_that_answers_a_pull_closes_its_command(): void
    {
        $this->office();
        $device = $this->device();

        app(PullFingerprintsService::class)->forPin($device, '1234', [0]);

        $this->postCdata(
            '/iclock/cdata?SN=SN-1&table=OPERLOG',
            "FP PIN=1234\tFID=0\tSize=1404\tValid=1\tTMP=QUJDREVGRw=="
        );

        $this->assertSame('query', FingerprintTemplate::first()->source);
        $this->assertNotNull(Command::first()->completed_at);
    }

    public function test_re_uploading_the_same_template_does_not_duplicate_it(): void
    {
        $this->office();
        $this->device();

        $body = "FP PIN=1234\tFID=0\tSize=1404\tValid=1\tTMP=QUJDREVGRw==";

        $this->postCdata('/iclock/cdata?SN=SN-1&table=OPERLOG', $body);
        $this->postCdata('/iclock/cdata?SN=SN-1&table=OPERLOG', $body);

        $this->assertDatabaseCount('fingerprint_templates', 1);
    }

    public function test_a_changed_template_replaces_the_stored_one(): void
    {
        $this->office();
        $this->device();

        $this->postCdata('/iclock/cdata?SN=SN-1&table=OPERLOG', "FP PIN=1\tFID=0\tSize=4\tValid=1\tTMP=QQ==");
        $this->postCdata('/iclock/cdata?SN=SN-1&table=OPERLOG', "FP PIN=1\tFID=0\tSize=4\tValid=1\tTMP=Qg==");

        $this->assertDatabaseCount('fingerprint_templates', 1);
        $this->assertSame('Qg==', FingerprintTemplate::first()->template);
    }

    public function test_it_indexes_captured_fingers_on_the_employee(): void
    {
        $this->office();
        $this->device();

        Agente::create(['idempresa' => 1, 'idoficina' => 2, 'idagente' => 1234, 'shortname' => 'x', 'fullname' => 'X']);

        $this->postCdata('/iclock/cdata?SN=SN-1&table=OPERLOG', "FP PIN=1234\tFID=0\tSize=4\tValid=1\tTMP=QQ==");

        $index = json_decode(Agente::where('idagente', 1234)->first()->fingerprint_data, true);

        $this->assertIsArray($index);
        $this->assertSame(0, $index[0]['fid']);
        $this->assertSame('SN-1', $index[0]['sn']);
    }

    public function test_attendance_uploads_are_unaffected(): void
    {
        $this->office();
        $this->device();

        $this->postCdata(
            '/iclock/cdata?SN=SN-1&table=ATTLOG&Stamp=999',
            "1234\t2026-08-25 08:00:00\t1\t1\t0\t0\t0\r\n1235\t2026-08-25 08:05:00\t1\t1\t0\t0\t0"
        )->assertSee('OK: 2');

        $this->assertDatabaseCount('attendances', 2);
        $this->assertDatabaseHas('attendances', ['employee_id' => 1234, 'idoficina' => 2]);
        $this->assertDatabaseCount('fingerprint_templates', 0);
    }

    public function test_a_command_acknowledgement_is_recorded(): void
    {
        $this->office();
        $device = $this->device();

        app(PullFingerprintsService::class)->bulk($device);
        $command = $device->commands()->first();

        $this->call(
            'POST',
            '/iclock/devicecmd?SN=SN-1',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'text/plain'],
            "ID={$command->command}&Return=0&CMD=CHECK"
        )->assertOk();

        $command->refresh();

        $this->assertNotNull($command->completed_at);
        $this->assertNull($command->failed_at);
    }

    public function test_a_failed_command_acknowledgement_is_recorded_as_failed(): void
    {
        $this->office();
        $device = $this->device();

        app(PullFingerprintsService::class)->forPin($device, '1234', [0]);
        $command = $device->commands()->first();

        $this->call(
            'POST',
            '/iclock/devicecmd?SN=SN-1',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'text/plain'],
            "ID={$command->command}&Return=-1&CMD=DATA"
        );

        $command->refresh();

        $this->assertNotNull($command->failed_at);
        $this->assertNull($command->completed_at);
    }

    public function test_the_handshake_asks_the_terminal_to_push_enrolled_fingerprints(): void
    {
        $this->office();
        $this->device();

        $response = $this->get('/iclock/cdata?SN=SN-1&options=all');

        // Positions 6 and 7 are EnrollFP / ChgFP.
        $response->assertSee('TransFlag=' . config('adms.trans_flag'));
        $this->assertSame('1', substr((string) config('adms.trans_flag'), 5, 1));
        $this->assertSame('1', substr((string) config('adms.trans_flag'), 6, 1));
    }

    public function test_getrequest_hands_out_commands_in_batches(): void
    {
        config(['adms.commands_per_request' => 3]);

        $this->office();
        $device = $this->device();

        app(PullFingerprintsService::class)->forPin($device, '1234', [0, 1, 2, 3, 4]);

        $response = $this->get('/iclock/getrequest?SN=SN-1');

        $this->assertCount(3, array_filter(explode("\r\n", $response->getContent())));
        $this->assertSame(2, $device->commands()->pending()->count());
    }

    public function test_push_queues_a_data_update_for_a_missing_template(): void
    {
        $this->office();
        $source = $this->device('SN-1');
        $target = $this->device('SN-2');

        FingerprintTemplate::create([
            'device_id' => $source->id,
            'sn' => 'SN-1',
            'pin' => '1234',
            'fid' => 0,
            'size' => 12,
            'valid' => 1,
            'template' => 'QUJDREVGRw==',
            'template_hash' => sha1('QUJDREVGRw=='),
            'idempresa' => '1',
            'idoficina' => '2',
            'source' => 'query',
            'captured_at' => now(),
        ]);

        $result = app(PushFingerprintsService::class)->toDevice($target);

        $this->assertSame(1, $result['commands']);

        $command = $target->commands()->first();

        $this->assertStringContainsString("DATA UPDATE FINGERTMP PIN=1234\tFID=0", $command->data);
        $this->assertStringContainsString('TMP=QUJDREVGRw==', $command->data);
    }

    public function test_push_skips_a_template_the_device_already_holds(): void
    {
        $this->office();
        $device = $this->device('SN-1');

        FingerprintTemplate::create([
            'device_id' => $device->id,
            'sn' => 'SN-1',
            'pin' => '1234',
            'fid' => 0,
            'size' => 12,
            'valid' => 1,
            'template' => 'QUJDREVGRw==',
            'template_hash' => sha1('QUJDREVGRw=='),
            'idempresa' => '1',
            'idoficina' => '2',
            'source' => 'push',
            'captured_at' => now(),
        ]);

        $result = app(PushFingerprintsService::class)->toDevice($device);

        $this->assertSame(0, $result['commands']);
        $this->assertSame(1, $result['skipped']);
    }

    public function test_push_never_distributes_an_invalid_template(): void
    {
        $this->office();
        $this->device('SN-1');
        $target = $this->device('SN-2');

        FingerprintTemplate::create([
            'sn' => 'SN-1',
            'pin' => '1234',
            'fid' => 0,
            'size' => 12,
            'valid' => 0,
            'template' => 'QUJDREVGRw==',
            'template_hash' => sha1('QUJDREVGRw=='),
            'idempresa' => '1',
            'idoficina' => '2',
            'source' => 'push',
            'captured_at' => now(),
        ]);

        $this->assertSame(0, app(PushFingerprintsService::class)->toDevice($target)['commands']);
    }
}

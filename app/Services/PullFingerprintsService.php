<?php

namespace App\Services;

use App\Models\Agente;
use App\Models\Device;
use App\Models\Oficina;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Queues the ADMS commands that make a terminal hand its fingerprint and face
 * templates back to the server.
 *
 * Two mechanisms, because neither alone is enough in practice:
 *
 *  - Bulk (`CHECK`): the terminal re-uploads the data it holds. One command,
 *    no knowledge of the roster needed — the right tool for adopting a device
 *    that was already enrolled in the field. What exactly a `CHECK` re-sends
 *    depends on firmware and on the TransFlag from the handshake, so it is
 *    best-effort.
 *
 *  - Targeted (`DATA QUERY FINGERTMP PIN=x FingerID=n`): asks for one finger of
 *    one employee. Precise and firmware-independent, but costs one command per
 *    finger, so pulling a full office this way is expensive.
 *
 *  - Faces (`DATA QUERY BIODATA Type=n [PIN=x]`): the unified-template query,
 *    one command per face type for a whole terminal or for one employee. Every
 *    pull above also queues these unless adms.pull_faces is off.
 *
 * Either way the templates come back asynchronously: the terminal POSTs them to
 * /iclock/cdata, where iclockController hands them to FingerprintIngestService.
 * Nothing here blocks waiting for data.
 *
 * @author XMindware
 */
class PullFingerprintsService
{
    public const TYPE_CHECK = 'pull_check';
    public const TYPE_QUERY_FP = 'pull_fingertmp';
    public const TYPE_QUERY_USER = 'pull_userinfo';
    public const TYPE_QUERY_FACE = 'pull_face';

    protected CommandIdService $commandIdService;

    public function __construct(?CommandIdService $commandIdService = null)
    {
        $this->commandIdService = $commandIdService ?? app(CommandIdService::class);
    }

    /**
     * Bulk pull: ask the terminal to re-upload everything it holds.
     *
     * @return int number of commands queued
     */
    public function bulk(Device $device): int
    {
        $queued = 0;

        $queued += $this->queue(
            $device,
            fn (int $cmdId) => "C:{$cmdId}:CHECK",
            self::TYPE_CHECK,
            'device:' . $device->id
        );

        if (config('adms.query_userinfo', true)) {
            $queued += $this->queue(
                $device,
                fn (int $cmdId) => "C:{$cmdId}:DATA QUERY USERINFO PIN=",
                self::TYPE_QUERY_USER,
                'device:' . $device->id
            );
        }

        $queued += $this->faces($device);

        Log::info('PullFingerprintsService: bulk pull queued', [
            'device_id' => $device->id,
            'sn' => $device->serial_number,
            'commands' => $queued,
        ]);

        return $queued;
    }

    /**
     * Ask a terminal for the face templates it holds.
     *
     * With no PINs this is one command per face type for the whole terminal;
     * with PINs, one per type per employee. Terminals that have no faces of a
     * given type (or no face support at all) just fail the command, which is
     * recorded on /iclock/devicecmd like any other.
     *
     * Comparison photos (BIOPHOTO) have no query of their own here — they are
     * uploaded on enrolment when the handshake TransFlag asks for them.
     *
     * @param iterable<int|string>|null $pins null = every face on the terminal
     * @return int number of commands queued (0 when adms.pull_faces is off)
     */
    public function faces(Device $device, ?iterable $pins = null): int
    {
        if (!config('adms.pull_faces', true)) {
            return 0;
        }

        $types = array_values(array_unique(array_map(
            'intval',
            (array) config('adms.face_types', BiometricRecordParser::TYPES_FACE)
        )));

        $queued = 0;

        if ($pins === null) {
            foreach ($types as $type) {
                $queued += $this->queue(
                    $device,
                    fn (int $cmdId) => "C:{$cmdId}:DATA QUERY BIODATA Type={$type}",
                    self::TYPE_QUERY_FACE,
                    "TYPE:{$type}"
                );
            }
        } else {
            foreach ($pins as $pin) {
                $pin = trim((string) $pin);

                // A PIN ends up inside a command line; never let one break it.
                if ($pin === '' || preg_match('/\s/', $pin)) {
                    continue;
                }

                foreach ($types as $type) {
                    $queued += $this->queue(
                        $device,
                        fn (int $cmdId) => "C:{$cmdId}:DATA QUERY BIODATA Type={$type}\tPIN={$pin}",
                        self::TYPE_QUERY_FACE,
                        "PIN:{$pin}/TYPE:{$type}"
                    );
                }
            }
        }

        Log::info('PullFingerprintsService: face pull queued', [
            'device_id' => $device->id,
            'sn' => $device->serial_number,
            'commands' => $queued,
        ]);

        return $queued;
    }

    /**
     * Targeted pull for a set of employees on one device.
     *
     * @param iterable<Agente>|null $employees defaults to the device's office roster
     * @param array<int, int>|null  $fids      finger slots, defaults to config('adms.finger_ids')
     * @return int number of commands queued
     */
    public function forDevice(Device $device, $employees = null, ?array $fids = null): int
    {
        $employees = $this->resolveEmployees($device, $employees);

        if ($employees->isEmpty()) {
            Log::info('PullFingerprintsService: no employees to pull', [
                'device_id' => $device->id,
            ]);

            return 0;
        }

        $fids = $this->resolveFingerIds($fids);
        $budget = (int) config('adms.max_pull_commands', 2000);
        $queued = 0;

        foreach ($employees as $employee) {
            $pin = $employee->idagente ?? null;

            if ($pin === null || $pin === '') {
                continue;
            }

            foreach ($fids as $fid) {
                if ($queued >= $budget) {
                    Log::warning('PullFingerprintsService: command budget reached', [
                        'device_id' => $device->id,
                        'budget' => $budget,
                    ]);

                    return $queued;
                }

                $queued += $this->queue(
                    $device,
                    fn (int $cmdId) => "C:{$cmdId}:DATA QUERY FINGERTMP PIN={$pin}\tFingerID={$fid}",
                    self::TYPE_QUERY_FP,
                    "PIN:{$pin}/FID:{$fid}"
                );
            }
        }

        Log::info('PullFingerprintsService: targeted pull queued', [
            'device_id' => $device->id,
            'sn' => $device->serial_number,
            'employees' => $employees->count(),
            'fingers' => count($fids),
            'commands' => $queued,
        ]);

        return $queued;
    }

    /**
     * Targeted pull for a single PIN on one device.
     *
     * @param array<int, int>|null $fids
     */
    public function forPin(Device $device, $pin, ?array $fids = null): int
    {
        $employee = Agente::withTrashed()
            ->where('idagente', $pin)
            ->where('idempresa', $device->idempresa)
            ->where('idoficina', $device->idoficina)
            ->first();

        // The PIN may exist on the terminal without a local roster row (that is
        // exactly the case worth pulling), so fall back to a bare stand-in.
        $employee ??= new Agente(['idagente' => $pin]);

        return $this->forDevice($device, collect([$employee]), $fids);
    }

    /**
     * Pull across every device in an office.
     *
     * @param array<int, int>|null $fids
     * @return array{devices: int, commands: int}
     */
    public function forOffice(Oficina $oficina, bool $bulk = true, ?array $fids = null): array
    {
        $devices = Device::where('idempresa', $oficina->idempresa)
            ->where('idoficina', $oficina->idoficina)
            ->get();

        $commands = 0;

        foreach ($devices as $device) {
            $commands += $bulk
                ? $this->bulk($device)
                : $this->forDevice($device, null, $fids);
        }

        return [
            'devices' => $devices->count(),
            'commands' => $commands,
        ];
    }

    /**
     * Create one command unless an identical one is already pending for this
     * device — a second click should not double the queue.
     *
     * @param callable(int): string $builder
     * @return int 1 when queued, 0 when skipped as duplicate
     */
    protected function queue(Device $device, callable $builder, string $type, ?string $reference = null): int
    {
        $alreadyPending = $device->commands()
            ->pending()
            ->where('type', $type)
            ->when($reference !== null, fn ($q) => $q->where('reference', $reference))
            ->exists();

        if ($alreadyPending) {
            Log::debug('PullFingerprintsService: command already pending, skipping', [
                'device_id' => $device->id,
                'type' => $type,
                'reference' => $reference,
            ]);

            return 0;
        }

        $cmdId = $this->commandIdService->getNextCmdId();

        $device->commands()->create([
            'device_id' => $device->id,
            'command' => $cmdId,
            'type' => $type,
            'reference' => $reference,
            'data' => $builder($cmdId),
            'executed_at' => null,
        ]);

        return 1;
    }

    /**
     * @param iterable<Agente>|null $employees
     * @return Collection<int, Agente>
     */
    protected function resolveEmployees(Device $device, $employees): Collection
    {
        if ($employees instanceof Collection) {
            return $employees->values();
        }

        if ($employees !== null) {
            return collect($employees)->values();
        }

        return Agente::where('idempresa', $device->idempresa)
            ->where('idoficina', $device->idoficina)
            ->get()
            ->values();
    }

    /**
     * @param array<int, int>|null $fids
     * @return array<int, int>
     */
    protected function resolveFingerIds(?array $fids): array
    {
        $fids ??= config('adms.finger_ids', range(0, 9));

        $fids = array_values(array_unique(array_map('intval', (array) $fids)));

        return array_values(array_filter($fids, fn (int $fid) => $fid >= 0 && $fid <= 9));
    }
}

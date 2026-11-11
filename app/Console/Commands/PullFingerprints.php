<?php

namespace App\Console\Commands;

use App\Models\Device;
use App\Models\Oficina;
use App\Services\PullFingerprintsService;
use Illuminate\Console\Command;

/**
 * Ask terminals for their fingerprint (and face) templates.
 *
 *   php artisan fingerprints:pull --device=3
 *   php artisan fingerprints:pull --device=3 --pin=1234
 *   php artisan fingerprints:pull --office=20 --roster
 *   php artisan fingerprints:pull --all
 *
 * Queues commands and returns; templates arrive later over /iclock/cdata.
 *
 * @author XMindware
 */
class PullFingerprints extends Command
{
    protected $signature = 'fingerprints:pull
        {--device= : Device id or serial number}
        {--office= : idoficina — every device in that office}
        {--all : Every registered device}
        {--pin= : Single employee PIN (implies targeted mode)}
        {--roster : Targeted pull for the whole office roster instead of a bulk CHECK}
        {--fingers= : Comma-separated finger ids, e.g. 0,1 (targeted mode only)}';

    protected $description = 'Queue commands asking terminals to upload their fingerprint and face templates.';

    public function handle(PullFingerprintsService $service): int
    {
        $devices = $this->resolveDevices();

        if ($devices->isEmpty()) {
            $this->error('No devices matched. Pass --device, --office or --all.');

            return self::FAILURE;
        }

        $fids = $this->option('fingers')
            ? array_map('intval', explode(',', (string) $this->option('fingers')))
            : null;

        $pin = $this->option('pin');
        $roster = (bool) $this->option('roster');
        $total = 0;

        foreach ($devices as $device) {
            if ($pin) {
                $queued = $service->forPin($device, $pin, $fids) + $service->faces($device);
                $mode = "PIN {$pin}";
            } elseif ($roster) {
                $queued = $service->forDevice($device, null, $fids) + $service->faces($device);
                $mode = 'roster';
            } else {
                $queued = $service->bulk($device);
                $mode = 'bulk';
            }

            $total += $queued;

            $this->line(sprintf(
                '%-24s %-8s %d command(s)%s',
                $device->name ?: $device->serial_number,
                $mode,
                $queued,
                $device->online && $device->online->diffInMinutes(now()) <= 5 ? '' : '  (device looks offline)'
            ));
        }

        $this->info("Queued {$total} command(s) across {$devices->count()} device(s).");
        $this->comment('Templates arrive asynchronously — check `fingerprints` in the UI in a minute or two.');

        return self::SUCCESS;
    }

    /**
     * @return \Illuminate\Support\Collection<int, Device>
     */
    protected function resolveDevices()
    {
        if ($this->option('all')) {
            return Device::orderBy('id')->get();
        }

        if ($office = $this->option('office')) {
            $oficina = Oficina::where('idoficina', $office)->first();

            if (!$oficina) {
                return collect();
            }

            return Device::where('idempresa', $oficina->idempresa)
                ->where('idoficina', $oficina->idoficina)
                ->get();
        }

        if ($device = $this->option('device')) {
            return Device::where('id', $device)
                ->orWhere('serial_number', $device)
                ->get();
        }

        return collect();
    }
}

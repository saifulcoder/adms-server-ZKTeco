<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Device;
use Carbon\Carbon;

class MonitorDeviceAlerts extends Command
{
    protected $signature = 'devices:check-status';
    protected $description = 'Send alerts when devices go offline too long or come back online';

    public function handle()
    {
        $threshold = now()->subMinutes(3);

        // 🔌 Devices that have been offline for more than 3 minutes
        Device::where('online', '<', $threshold)->get()->each(function ($device) {
            if (!$device->last_alert_sent_at || $device->last_alert_sent_at->lt($device->online)) {
                Log::channel('slack')->warning("🔌 Device `{$device->name}` (ID: {$device->id}) has been offline since {$device->online}.");
                $device->last_alert_sent_at = now();
                $device->save();
            }
        });

        // ✅ Devices back online after being previously offline
        Device::where('online', '>=', $threshold)->get()->each(function ($device) {
            if ($device->last_alert_sent_at && Carbon::parse($device->last_alert_sent_at)->gt($device->online)) {
                Log::channel('slack')->info("✅ Device `{$device->name}` (ID: {$device->id}) is back online as of {$device->online}.");
                $device->last_alert_sent_at = null;
                $device->save();
            }
        });
    }
}

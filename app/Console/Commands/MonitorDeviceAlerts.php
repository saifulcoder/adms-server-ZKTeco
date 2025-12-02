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

        // 🔌 OFFLINE ALERTS
        Device::where('online', false)
            ->where('last_online_at', '<', $threshold)
            ->get()
            ->each(function ($device) {
                if (!$device->last_alert_sent_at || $device->last_alert_sent_at->lt($device->last_online_at)) {
                    Log::channel('slack')->warning("🔌 Device `{$device->name}` (ID: {$device->id}) has been offline since {$device->last_online_at}.");
                    $device->last_alert_sent_at = now();
                    $device->save();
                }
            });

        // ✅ BACK ONLINE ALERTS
        Device::where('online', true)
            ->get()
            ->each(function ($device) {
                if ($device->last_alert_sent_at && $device->last_alert_sent_at->gt($device->last_online_at)) {
                    Log::channel('slack')->info("✅ Device `{$device->name}` (ID: {$device->id}) is back online as of {$device->last_online_at}.");
                    $device->last_alert_sent_at = null;
                    $device->save();
                }
            });
    }
}

<?php

namespace App\Services;

use App\Models\Command;
use App\Models\Setting; // or wherever you store 'last_cmd_id' persistent data
use Illuminate\Support\Facades\DB;
use Log;

class CommandIdService
{
    /**
     * Generate the next CmdID, cycling from 1..10000.
     */
    public function getNextCmdId(): int
    {
        // Retrieve last used from your 'settings' table or somewhere you store it
        $lastCommandId = Command::orderBy('id', 'desc')->value('command') ?? 0;
        
        // If not set, default to 0
        if (!$lastCommandId) {
            $lastCommandId = 0;
        }

        // Increment
        $newCmdId = $lastCommandId + 1;

        // Wrap if above 10000
        if ($newCmdId > 10000) {
            $newCmdId = 1;
        }

        // Optional: Avoid reusing an ID that is still "pending"
        while ($this->isPending($newCmdId)) {
            $newCmdId++;
            if ($newCmdId > 10000) {
                $newCmdId = 1;
            }
        }

        return $newCmdId;
    }

    protected function isPending(int $cmdId): bool
    {
        return Command::where('command', $cmdId)
                      ->whereNull('executed_at') // or however you track "pending"
                      ->exists();
    }
}

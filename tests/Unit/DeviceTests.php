<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class DeviceTests extends TestCase
{
    /**
     * A basic test example.
     */
    
    public function test_device_log_has_correct_fillable_properties(): void
    {
        $deviceLog = new \App\Models\DeviceLog();
        $this->assertEquals(
            ['id', 'data', 'tgl', 'sn', 'option', 'url'],
            $deviceLog->getFillable()
        );
    }
}

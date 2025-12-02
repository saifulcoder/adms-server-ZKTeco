<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClockCommTest extends TestCase
{
    /**
     * test the clock/test
     */
    public function test_iclock_test_returns_200(): void
    {
        $response = $this->get('/iclock/test');

        $response->assertStatus(200);
    }


    /**
     * test the clock/cdata
     */
    public function test_iclock_getrequest_returns_200(): void
    {
        $response = $this->get('/iclock/getrequest');

        $response->assertStatus(200);
    }
}

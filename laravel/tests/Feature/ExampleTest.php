<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // Skip when legacy tables are not available (e.g. SQLite :memory: test DB)
        try {
            DB::table('general_settings')->first();
        } catch (\Exception $e) {
            $this->markTestSkipped('Legacy database tables not available: ' . $e->getMessage());
        }

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}

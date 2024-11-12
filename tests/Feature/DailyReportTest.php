<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DailyReportTest extends TestCase
{
    use RefreshDatabase;
    public function test_get_daily_report(): void
    {
        $user = User::factory()->create(['role'=>'admin']);
    $response = $this->actingAs($user, 'api') ->get('/api/daily-report');
        $response->assertStatus(200);
    }
}

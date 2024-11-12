<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_get_all_users(): void
    {
        $user = User::factory()->create(['role'=>'admin']);
        $response = $this->actingAs($user, 'api') ->get('/api/users');
        $response->assertStatus(200);
    }
    public function test_non_admin_cannot_get_all_users(): void
    {
        $user = User::factory()->create(['role'=>'developer']);
        $response = $this->actingAs($user, 'api') ->get('/api/users');
        $response->assertStatus(403);
    }
    public function test_admin_can_create_new_user(): void
    {
        $user = User::factory()->create(['role'=>'admin']);
        $response = $this->actingAs($user, 'api') ->post('/api/users',[
            'name'=>'x',
            'email'=>'test2@example.com',
            'password'=>'12345678',
             'password_confirmation'=>'12345678',
            'role'=>'developer'
        ]);
        $response->assertStatus(200);
    }
    public function test_admin_can_update_user(): void
    {
        $admin = User::factory()->create(['role'=>'admin']);
        $user = User::factory()->create(['role'=>'developer']);

        $response = $this->actingAs($admin, 'api') ->put('/api/users/'.$user->id,[
            'email'=>'update@example.com',
            'role'=>'manager'
        ]);
        $response->assertStatus(200);
    }
    public function test_admin_can_show_user(): void
    {
        $admin = User::factory()->create(['role'=>'admin']);
        $user = User::factory()->create(['role'=>'developer']);

        $response = $this->actingAs($admin, 'api') ->get('/api/users/'.$user->id);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'name',
                'email',
                'role',
            ]
        ]);
    }
    public function test_admin_can_delete_user(): void
    {
        $admin = User::factory()->create(['role'=>'admin']);
        $user = User::factory()->create(['role'=>'developer']);
        $response = $this->actingAs($admin, 'api') ->delete('/api/users/'.$user->id);
        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }
}

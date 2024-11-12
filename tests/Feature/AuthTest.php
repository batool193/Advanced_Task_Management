<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthTest extends TestCase
{  use RefreshDatabase;
    public function test_user_can_login_successfully(): void
    {
        $user = User::factory()->create([
            'name'=>'test',
            'email'=>'test@example.com',
            'password'=>'12345678',
            'role'=>'admin'
        ]);
        $response = $this->post('/api/login',['email'=>'test@example.com','password'=>'12345678']);
        $this->assertAuthenticatedAs($user,'api');
        $response->assertStatus(200);
    }
    public function test_user_login_with_invailed_credentials(): void
    {
        $response = $this->post('/api/login',['email'=>'test1@example.com']);
        $this->assertGuest();
        $response->assertStatus(403);
    }
   public function test_user_can_logout_successfully()
   {
    $user = User::factory()->create();
   $this->actingAs($user)->post('/api/login',['email'=>$user->email,'password'=>$user->password]);
   $this->assertAuthenticatedAs($user,'api');
   $token = JWTAuth::fromUser($user);
     $response = $this->actingAs($user)->withToken($token,'bearer')->post('api/logout');
        $response->assertStatus(200);
   }

    }


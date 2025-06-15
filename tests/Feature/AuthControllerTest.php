<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    public function test_user_can_login()
    {
        // Membuat user di database
        \App\Models\User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Mengirim request login
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Memastikan status response adalah 200 (OK)
        $response->assertStatus(200);

        // Memastikan response JSON memiliki token
        $response->assertJsonStructure([
            'access_token',
            'token_type',
            'expires_in',
        ]);
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);

        // Memastikan status response adalah 401 (Unauthorized)
        $response->assertStatus(401);
    }

    public function test_user_can_logout()
    {
        // Membuat user dan login
        $user = \App\Models\User::factory()->create();
        $token = $user->createToken('authToken')->plainTextToken;

        // Mengirim request logout
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        // Memastikan status response adalah 200 (OK)
        $response->assertStatus(200);

        // Memastikan response JSON berisi pesan sukses
        $response->assertJson(['message' => 'Successfully logged out']);
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase; // Menggunakan database transaksi untuk reset setiap test

    /**
     * Test user registration.
     *
     * @return void
     */
    public function test_user_can_register()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        // Memastikan status response adalah 201 (Created)
        $response->assertStatus(201);

        // Memastikan response JSON memiliki struktur yang benar
        $response->assertJsonStructure([
            'message',
            'token',
        ]);

        // Memastikan user berhasil disimpan di database
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);
    }

    /**
     * Test user cannot register with invalid data.
     *
     * @return void
     */
    public function test_user_cannot_register_with_invalid_data()
    {
        $response = $this->postJson('/api/register', [
            'name' => '', // Invalid: name kosong
            'email' => 'invalid-email', // Invalid: format email salah
            'password' => '123', // Invalid: password kurang dari 6 karakter
        ]);

        // Memastikan status response adalah 422 (Unprocessable Entity)
        $response->assertStatus(422);

        // Memastikan response JSON berisi pesan error validasi
        $response->assertJsonValidationErrors(['name', 'email', 'password']);
    }
}

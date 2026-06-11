<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_register_as_inactive_mahasiswa_pending_approval(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'identity_number' => '210901501001',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // Tidak langsung login — menunggu aktivasi admin (PLAN F1.8)
        $this->assertGuest();
        $response->assertRedirect(route('login'));

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'identity_number' => '210901501001',
            'role' => User::ROLE_MAHASISWA,
            'is_active' => false,
        ]);
    }

    public function test_registration_requires_identity_number(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('identity_number');
        $this->assertGuest();
    }
}

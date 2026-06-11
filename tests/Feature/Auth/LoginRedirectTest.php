<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_is_redirected_to_filament_panel_after_login(): void
    {
        $admin = User::factory()->admin()->create();

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect('/admin');
    }

    public function test_mahasiswa_is_redirected_to_dashboard_after_login(): void
    {
        $mahasiswa = User::factory()->mahasiswa()->create();

        $this->post('/login', [
            'email' => $mahasiswa->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_dosen_is_redirected_to_dashboard_after_login(): void
    {
        $dosen = User::factory()->dosen()->create();

        $this->post('/login', [
            'email' => $dosen->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));
    }
}

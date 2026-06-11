<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth', 'role:admin,dosen'])
            ->get('/_uji/khusus-internal', fn () => 'ok');
    }

    public function test_allowed_roles_pass(): void
    {
        $this->actingAs(User::factory()->dosen()->create());

        $this->get('/_uji/khusus-internal')->assertOk();
    }

    public function test_disallowed_role_gets_403(): void
    {
        $this->actingAs(User::factory()->mahasiswa()->create());

        $this->get('/_uji/khusus-internal')->assertForbidden();
    }

    public function test_inactive_user_gets_403(): void
    {
        $this->actingAs(User::factory()->dosen()->inactive()->create());

        $this->get('/_uji/khusus-internal')->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/_uji/khusus-internal')->assertRedirect(route('login'));
    }
}

<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\Login as AdminLogin;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class DosenLoginReproTest extends TestCase
{
    use RefreshDatabase;

    public function test_dosen_created_via_filament_can_login_via_public_login(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Dr. Dosen Baru, M.M.',
                'email' => 'dosen.baru@unm.ac.id',
                'identity_number' => '0099887766',
                'role' => User::ROLE_DOSEN,
                'is_active' => true,
                'password' => 'RahasiaDosen#123',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $dosen = User::where('email', 'dosen.baru@unm.ac.id')->first();
        $this->assertNotNull($dosen, 'User dosen tidak terbuat');
        $this->assertTrue($dosen->is_active, 'is_active false padahal toggle on');
        $this->assertSame(User::ROLE_DOSEN, $dosen->role);
        $this->assertTrue(
            Hash::check('RahasiaDosen#123', $dosen->password),
            'Password tersimpan tidak cocok (kemungkinan double-hash atau plaintext)',
        );

        auth()->logout();

        // Login lewat halaman login publik (/login)
        $this->post('/login', [
            'email' => 'dosen.baru@unm.ac.id',
            'password' => 'RahasiaDosen#123',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($dosen);
    }

    public function test_dosen_logging_in_at_admin_login_is_redirected_to_dashboard(): void
    {
        $dosen = User::factory()->dosen()->create(['email' => 'dosen.lama@unm.ac.id']);

        Livewire::test(AdminLogin::class)
            ->fillForm([
                'email' => 'dosen.lama@unm.ac.id',
                'password' => 'password',
                'remember' => false,
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors()
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($dosen);
    }

    public function test_inactive_user_is_still_rejected_at_admin_login(): void
    {
        User::factory()->dosen()->inactive()->create(['email' => 'nonaktif@unm.ac.id']);

        Livewire::test(AdminLogin::class)
            ->fillForm([
                'email' => 'nonaktif@unm.ac.id',
                'password' => 'password',
                'remember' => false,
            ])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);

        $this->assertGuest();
    }

    public function test_wrong_password_is_still_rejected_at_admin_login(): void
    {
        User::factory()->dosen()->create(['email' => 'dosen.salah@unm.ac.id']);

        Livewire::test(AdminLogin::class)
            ->fillForm([
                'email' => 'dosen.salah@unm.ac.id',
                'password' => 'password-keliru',
                'remember' => false,
            ])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);

        $this->assertGuest();
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_mahasiswa_cannot_access_admin_panel(): void
    {
        $this->actingAs(User::factory()->mahasiswa()->create());

        $this->get('/admin')->assertForbidden();
    }

    public function test_dosen_cannot_access_admin_panel(): void
    {
        $this->actingAs(User::factory()->dosen()->create());

        $this->get('/admin')->assertForbidden();
    }

    public function test_inactive_admin_cannot_access_admin_panel(): void
    {
        $this->actingAs(User::factory()->admin()->inactive()->create());

        $this->get('/admin')->assertForbidden();
    }

    public function test_admin_can_access_dashboard_and_resources(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->get('/admin')->assertOk();
        $this->get('/admin/documents')->assertOk();
        $this->get('/admin/documents/create')->assertOk();
        $this->get('/admin/categories')->assertOk();
        $this->get('/admin/users')->assertOk();
    }

    public function test_sidebar_shows_main_category_menu(): void
    {
        $this->seed(CategorySeeder::class);
        $this->actingAs(User::factory()->admin()->create());

        $this->get('/admin')
            ->assertOk()
            ->assertSee('Kategori Arsip')
            ->assertSee('Profil dan Dokumen Dasar')
            ->assertSee('Dokumentasi');
    }
}

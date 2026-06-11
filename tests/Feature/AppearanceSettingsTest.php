<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Support\ColorPalette;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppearanceSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_theme_css_variables_are_injected_from_settings(): void
    {
        Setting::set('primary_color', '#10B981');

        $expected = ColorPalette::shades('#10B981');

        $this->get('/')
            ->assertOk()
            ->assertSee('--unm-500: '.$expected[500], false)
            ->assertSee('--unm-900: '.$expected[900], false);
    }

    public function test_site_identity_settings_change_public_pages(): void
    {
        Setting::set('site_name', 'ArsipKu');
        Setting::set('site_owner', 'Prodi Uji Coba');

        $this->get('/')
            ->assertOk()
            ->assertSee('ArsipKu')
            ->assertSee('Prodi Uji Coba');
    }

    public function test_invalid_color_falls_back_to_navy(): void
    {
        Setting::set('primary_color', 'bukan-warna');

        $navy = ColorPalette::shades('#1E3A8A');

        $this->get('/')
            ->assertOk()
            ->assertSee('--unm-500: '.$navy[500], false);
    }

    public function test_hero_uses_gradient_when_no_image_set(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('bg-gradient-to-br', false)
            ->assertDontSee('background-image', false);
    }

    public function test_hero_uses_photo_with_color_overlay_when_set(): void
    {
        Setting::set('hero_image_path', 'hero/kampus-unm.jpg');
        Setting::set('hero_overlay_color', '#000000');
        Setting::set('hero_overlay_opacity', '60');

        $this->get('/')
            ->assertOk()
            ->assertSee('background-image', false)
            ->assertSee('/storage/hero/kampus-unm.jpg', false)
            ->assertSee('rgb(0 0 0 / 0.6)', false)
            ->assertDontSee('bg-gradient-to-br', false);
    }

    public function test_hero_overlay_opacity_is_clamped(): void
    {
        Setting::set('hero_image_path', 'hero/kampus-unm.jpg');
        Setting::set('hero_overlay_opacity', '250'); // nilai liar → dibatasi 100

        $this->get('/')
            ->assertOk()
            ->assertSee('/ 1)', false);
    }

    public function test_admin_can_open_appearance_settings_page(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->get('/admin/appearance-settings')
            ->assertOk()
            ->assertSee('Pengaturan Tampilan');
    }

    public function test_non_admin_cannot_open_appearance_settings_page(): void
    {
        $this->actingAs(User::factory()->dosen()->create());

        $this->get('/admin/appearance-settings')->assertForbidden();
    }
}

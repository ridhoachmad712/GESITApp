<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_hero_texts_can_be_overridden_from_settings(): void
    {
        Setting::set('hero_title', 'Arsip Digital Kebanggaan Prodi');
        Setting::set('hero_description', 'Deskripsi hero hasil suntingan admin.');
        Setting::set('hero_search_placeholder', 'Ketik judul di sini…');

        $this->get('/')
            ->assertOk()
            ->assertSee('Arsip Digital Kebanggaan Prodi')
            ->assertSee('Deskripsi hero hasil suntingan admin.')
            ->assertSee('Ketik judul di sini…');
    }

    public function test_search_chips_come_from_settings(): void
    {
        Setting::set('hero_search_chips', json_encode(['Skripsi', 'Jurnal']));

        $this->get('/')
            ->assertOk()
            ->assertSee('/cari?q=Skripsi', false)
            ->assertSee('/cari?q=Jurnal', false)
            ->assertDontSee('/cari?q=Kurikulum', false);
    }

    public function test_empty_chips_hide_the_popular_row(): void
    {
        Setting::set('hero_search_chips', '[]');

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Populer:');
    }

    public function test_section_titles_order_and_visibility_follow_settings(): void
    {
        Setting::set('section_latest_title', 'Baru Diunggah Minggu Ini');
        Setting::set('section_latest_order', '1');
        Setting::set('section_categories_order', '9');
        Setting::set('section_featured_visible', '0');

        $this->get('/')
            ->assertOk()
            // Judul kustom + urutan: terbaru tampil sebelum kategori
            ->assertSeeInOrder(['Baru Diunggah Minggu Ini', 'Jelajahi Kategori Arsip']);
    }

    public function test_hidden_section_is_not_rendered(): void
    {
        Setting::set('section_latest_visible', '0');

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Dokumen Terbaru');
    }

    public function test_login_banner_text_and_toggle_follow_settings(): void
    {
        Setting::set('login_banner_text', 'Khusus civitas: silakan masuk dahulu.');
        Setting::set('login_banner_button', 'Login Sekarang');

        $this->get('/')
            ->assertSee('Khusus civitas: silakan masuk dahulu.')
            ->assertSee('Login Sekarang');

        Setting::set('login_banner_enabled', '0');

        $this->get('/')
            ->assertDontSee('Khusus civitas: silakan masuk dahulu.');
    }

    public function test_navbar_order_follows_settings(): void
    {
        Setting::set('nav_cari_order', '0');

        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['Pencarian', 'Arsip', 'Beranda']);
    }

    public function test_footer_contact_comes_from_settings(): void
    {
        Setting::set('footer_contact_line1', 'Gedung FEB Lantai 2');
        Setting::set('footer_link_label', 'manajemen.feb.unm.ac.id');
        Setting::set('footer_link_url', 'https://manajemen.feb.unm.ac.id');

        $this->get('/')
            ->assertSee('Gedung FEB Lantai 2')
            ->assertSee('manajemen.feb.unm.ac.id');
    }

    public function test_admin_can_open_homepage_content_page(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->get('/admin/homepage-content')
            ->assertOk()
            ->assertSee('Konten Beranda');
    }

    public function test_non_admin_cannot_open_homepage_content_page(): void
    {
        $this->actingAs(User::factory()->dosen()->create());

        $this->get('/admin/homepage-content')->assertForbidden();
    }
}

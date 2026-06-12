<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Document;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchUxTest extends TestCase
{
    use RefreshDatabase;

    public function test_suggest_endpoint_returns_max_five_visible_titles(): void
    {
        Document::factory()->count(7)->create(['title' => 'Panduan KP Saran '.fake()->unique()->word()]);
        Document::factory()->visibility(Document::VISIBILITY_INTERNAL)
            ->create(['title' => 'Panduan KP Rahasia Internal']);

        $response = $this->getJson('/cari/saran?q=Panduan KP');

        $response->assertOk();
        $this->assertCount(5, $response->json());
        $this->assertStringNotContainsString('Rahasia Internal', $response->getContent());
    }

    public function test_suggest_requires_two_characters(): void
    {
        Document::factory()->create(['title' => 'Apa Saja']);

        $this->getJson('/cari/saran?q=A')->assertOk()->assertExactJson([]);
    }

    public function test_suggest_can_be_scoped_to_category(): void
    {
        $target = Category::factory()->create(['slug' => 'target-saran']);
        Document::factory()->create(['category_id' => $target->id, 'title' => 'Panduan KP Dalam Target']);
        Document::factory()->create(['title' => 'Panduan KP Di Luar Target']);

        $response = $this->getJson('/cari/saran?q=Panduan KP&kategori=target-saran');

        $this->assertCount(1, $response->json());
        $this->assertStringContainsString('Dalam Target', $response->getContent());
    }

    public function test_search_results_highlight_keywords(): void
    {
        Document::factory()->create(['title' => 'Panduan KP Tersorot']);

        $this->get('/cari?q=KP')
            ->assertOk()
            ->assertSee('<mark', false)
            ->assertSee('KP</mark>', false);
    }

    public function test_search_year_filter_limits_results(): void
    {
        Document::factory()->create(['title' => 'Dok KP Lama', 'academic_year' => '2024/2025']);
        Document::factory()->create(['title' => 'Dok KP Baru', 'academic_year' => '2025/2026']);

        $this->get('/cari?q=KP&tahun=2024/2025')
            ->assertOk()
            ->assertSeeText('Dok KP Lama')
            ->assertDontSeeText('Dok KP Baru');
    }

    public function test_announcement_bar_follows_settings(): void
    {
        $this->get('/')->assertDontSee('bg-unm-900 px-4 py-2', false);

        Setting::set('announcement_enabled', '1');
        Setting::set('announcement_text', 'Pengisian KRS dibuka sampai 20 Juni.');
        Setting::set('announcement_link_url', 'https://feb.unm.ac.id/krs');
        Setting::set('announcement_link_label', 'Info KRS');

        $this->get('/')
            ->assertSee('Pengisian KRS dibuka sampai 20 Juni.')
            ->assertSee('Info KRS');

        // Tampil di semua halaman publik, bukan hanya beranda
        $this->get('/arsip')->assertSee('Pengisian KRS dibuka sampai 20 Juni.');
    }

    public function test_homepage_content_admin_page_still_works(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->get('/admin/homepage-content')
            ->assertOk()
            ->assertSee('Bar Pengumuman');
    }
}

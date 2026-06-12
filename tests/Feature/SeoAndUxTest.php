<?php

namespace Tests\Feature;

use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoAndUxTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_lists_public_documents_only(): void
    {
        $public = Document::factory()->create(['slug' => 'dokumen-publik-sitemap']);
        $internal = Document::factory()->visibility(Document::VISIBILITY_INTERNAL)
            ->create(['slug' => 'dokumen-internal-sitemap']);
        $draft = Document::factory()->draft()->create(['slug' => 'dokumen-draf-sitemap']);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee(route('home'), false)
            ->assertSee('dokumen-publik-sitemap', false)
            ->assertDontSee('dokumen-internal-sitemap', false)
            ->assertDontSee('dokumen-draf-sitemap', false)
            ->assertSee(route('arsip.show', $public->category), false);
    }

    public function test_pages_have_open_graph_and_json_ld(): void
    {
        $document = Document::factory()->create(['title' => 'Dokumen OG Uji']);

        $this->get('/')
            ->assertSee('og:site_name', false)
            ->assertSee('application/ld+json', false)
            ->assertSee('SearchAction', false);

        $this->get(route('documents.show', $document))
            ->assertSee('og:title', false)
            ->assertSee('Dokumen OG Uji — GESIT', false)
            ->assertSee('CreativeWork', false);
    }

    public function test_document_detail_has_share_buttons(): void
    {
        $document = Document::factory()->create(['title' => 'Dokumen Untuk Dibagikan']);

        $this->get(route('documents.show', $document))
            ->assertOk()
            ->assertSee('Salin Tautan')
            ->assertSee('wa.me', false);
    }

    public function test_layout_has_skip_link_and_back_to_top(): void
    {
        $this->get('/')
            ->assertSee('Lewati ke konten')
            ->assertSee('Kembali ke atas', false)
            ->assertSee('id="konten"', false);
    }

    public function test_homepage_cache_is_busted_when_document_changes(): void
    {
        // Permintaan pertama mengisi cache
        $this->get('/')->assertOk();

        // Dokumen baru harus langsung tampil (cache di-bust oleh event saved)
        Document::factory()->create(['title' => 'Dokumen Baru Setelah Cache']);

        $this->get('/')
            ->assertOk()
            ->assertSeeText('Dokumen Baru Setelah Cache');
    }
}

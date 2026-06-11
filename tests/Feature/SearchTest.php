<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Tests\TestCase;

/**
 * Memakai DatabaseTruncation (bukan RefreshDatabase) karena index
 * FULLTEXT InnoDB tidak melihat baris yang belum di-commit.
 */
class SearchTest extends TestCase
{
    use DatabaseTruncation;

    public function test_search_page_renders_without_query(): void
    {
        $this->get('/cari')
            ->assertOk()
            ->assertSee('Masukkan kata kunci');
    }

    public function test_fulltext_search_finds_public_documents_by_title(): void
    {
        Document::factory()->create(['title' => 'Kurikulum Merdeka Belajar 2025']);
        Document::factory()->create(['title' => 'Panduan Skripsi Mahasiswa']);

        $this->get('/cari?q=kurikulum')
            ->assertOk()
            ->assertSee('Kurikulum Merdeka Belajar 2025')
            ->assertDontSee('Panduan Skripsi Mahasiswa');
    }

    public function test_search_results_respect_visitor_visibility(): void
    {
        Document::factory()->create(['title' => 'Renstra Ringkasan Publik']);
        Document::factory()->visibility(Document::VISIBILITY_INTERNAL)
            ->create(['title' => 'Renstra Lengkap Internal']);

        // Pengunjung publik hanya melihat dokumen public
        $this->get('/cari?q=renstra')
            ->assertOk()
            ->assertSee('Renstra Ringkasan Publik')
            ->assertDontSee('Renstra Lengkap Internal');

        // Dosen melihat keduanya
        $this->actingAs(User::factory()->dosen()->create());

        $this->get('/cari?q=renstra')
            ->assertOk()
            ->assertSee('Renstra Ringkasan Publik')
            ->assertSee('Renstra Lengkap Internal');
    }

    public function test_draft_documents_never_appear_in_search(): void
    {
        Document::factory()->draft()->create(['title' => 'Borang Akreditasi Draf']);

        $this->actingAs(User::factory()->dosen()->create());

        $this->get('/cari?q=borang')
            ->assertOk()
            ->assertDontSee('Borang Akreditasi Draf');
    }

    public function test_short_query_falls_back_to_like_search(): void
    {
        Document::factory()->create(['title' => 'Panduan KP Lapangan']);

        $this->get('/cari?q=KP')
            ->assertOk()
            ->assertSee('Panduan KP Lapangan');
    }
}

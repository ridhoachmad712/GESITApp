<?php

namespace Tests\Feature;

use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('documents');
    }

    public function test_home_page_renders(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('GESIT')
            ->assertSee('Gerakan Sistem Informasi Terpadu');
    }

    public function test_home_page_shows_only_public_published_documents(): void
    {
        $publicDoc = Document::factory()->create(['title' => 'Kurikulum Publik Terbit']);
        $internalDoc = Document::factory()
            ->visibility(Document::VISIBILITY_INTERNAL)
            ->create(['title' => 'Rahasia Rapat Internal']);
        $draftDoc = Document::factory()->draft()->create(['title' => 'Draf Belum Terbit']);
        $mahasiswaDoc = Document::factory()
            ->visibility(Document::VISIBILITY_MAHASISWA)
            ->create(['title' => 'Modul Khusus Mahasiswa']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Kurikulum Publik Terbit')
            ->assertDontSee('Rahasia Rapat Internal')
            ->assertDontSee('Draf Belum Terbit')
            ->assertDontSee('Modul Khusus Mahasiswa');
    }

    public function test_featured_section_appears_only_with_featured_documents(): void
    {
        $this->get('/')->assertDontSee('Dokumen Unggulan');

        Document::factory()->create(['is_featured' => true, 'title' => 'Sertifikat Akreditasi Unggul']);

        $this->get('/')
            ->assertSee('Dokumen Unggulan')
            ->assertSee('Sertifikat Akreditasi Unggul');
    }
}

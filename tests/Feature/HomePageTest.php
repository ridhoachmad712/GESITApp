<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Document;
use App\Models\User;
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

    public function test_hero_shows_popular_search_chips(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Populer:')
            ->assertSee('/cari?q=Kurikulum', false)
            ->assertSee('/cari?q=RPS', false);
    }

    public function test_popular_downloads_section_appears_only_with_downloads(): void
    {
        $this->get('/')->assertDontSee('Paling Banyak Diunduh');

        Document::factory()->create([
            'title' => 'Panduan Akademik Favorit',
            'download_count' => 25,
        ]);

        $this->get('/')
            ->assertSee('Paling Banyak Diunduh')
            ->assertSee('Panduan Akademik Favorit');
    }

    public function test_login_banner_shown_to_guests_only(): void
    {
        $this->get('/')
            ->assertSee('Mahasiswa atau dosen prodi?');

        $this->actingAs(User::factory()->mahasiswa()->create());

        $this->get('/')
            ->assertDontSee('Mahasiswa atau dosen prodi?');
    }

    public function test_document_card_shows_file_type_badge(): void
    {
        Document::factory()->create(['title' => 'Dokumen PDF Badge']);
        Document::factory()->external()->create(['title' => 'Dokumen Drive Badge']);

        $this->get('/')
            ->assertSee('PDF')
            ->assertSee('Drive');
    }

    public function test_empty_categories_are_dimmed_and_pushed_below_filled_ones(): void
    {
        $empty = Category::factory()->create(['name' => 'Kategori Kosong Beranda', 'sort_order' => 1]);
        $filled = Category::factory()->create(['name' => 'Kategori Berisi Beranda', 'sort_order' => 2]);
        Document::factory()->create(['category_id' => $filled->id]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Belum ada dokumen publik')
            // Kategori berisi tampil lebih dulu walau sort_order lebih besar
            ->assertSeeInOrder(['Kategori Berisi Beranda', 'Kategori Kosong Beranda']);
    }
}

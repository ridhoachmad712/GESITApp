<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchivePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_archive_index_lists_root_categories(): void
    {
        Category::factory()->create(['name' => 'Arsip Akademik Uji']);

        $this->get('/arsip')
            ->assertOk()
            ->assertSee('Arsip Akademik Uji');
    }

    public function test_show_all_lists_documents_including_subcategories(): void
    {
        $parent = Category::factory()->create(['name' => 'Induk Uji']);
        $child = Category::factory()->create(['parent_id' => $parent->id]);

        Document::factory()->create(['category_id' => $parent->id, 'title' => 'Dokumen Induk Langsung']);
        Document::factory()->create(['category_id' => $child->id, 'title' => 'Dokumen Anak Kategori']);

        // Kategori utama ber-sub menampilkan hub; daftar lengkap lewat ?semua=1
        $this->get(route('arsip.show', ['category' => $parent, 'semua' => 1]))
            ->assertOk()
            ->assertSee('Dokumen Induk Langsung')
            ->assertSee('Dokumen Anak Kategori');
    }

    public function test_category_page_hides_restricted_documents_from_guests(): void
    {
        $category = Category::factory()->create();

        Document::factory()->create(['category_id' => $category->id, 'title' => 'Dokumen Terbuka Umum']);
        Document::factory()->visibility(Document::VISIBILITY_INTERNAL)
            ->create(['category_id' => $category->id, 'title' => 'Notulen Internal Prodi']);
        Document::factory()->draft()
            ->create(['category_id' => $category->id, 'title' => 'Draf Tersembunyi']);

        $this->get(route('arsip.show', $category))
            ->assertOk()
            ->assertSee('Dokumen Terbuka Umum')
            ->assertDontSee('Notulen Internal Prodi')
            ->assertDontSee('Draf Tersembunyi');
    }

    public function test_logged_in_mahasiswa_sees_mahasiswa_documents_on_category_page(): void
    {
        $category = Category::factory()->create();

        Document::factory()->visibility(Document::VISIBILITY_MAHASISWA)
            ->create(['category_id' => $category->id, 'title' => 'Modul Khusus Mahasiswa Uji']);

        $this->actingAs(User::factory()->mahasiswa()->create());

        $this->get(route('arsip.show', $category))
            ->assertOk()
            ->assertSee('Modul Khusus Mahasiswa Uji');
    }

    public function test_academic_year_filter_limits_results(): void
    {
        $category = Category::factory()->create();

        Document::factory()->create([
            'category_id' => $category->id,
            'title' => 'RPS Tahun Lama',
            'academic_year' => '2024/2025',
        ]);
        Document::factory()->create([
            'category_id' => $category->id,
            'title' => 'RPS Tahun Baru',
            'academic_year' => '2025/2026',
        ]);

        $this->get(route('arsip.show', ['category' => $category, 'tahun' => '2024/2025']))
            ->assertOk()
            ->assertSee('RPS Tahun Lama')
            ->assertDontSee('RPS Tahun Baru');
    }
}

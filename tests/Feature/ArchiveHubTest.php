<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchiveHubTest extends TestCase
{
    use RefreshDatabase;

    public function test_main_category_with_children_shows_hub_not_documents(): void
    {
        $parent = Category::factory()->create(['name' => 'Arsip Akademik Hub']);
        $child = Category::factory()->create(['parent_id' => $parent->id, 'name' => 'RPS Hub']);
        Document::factory()->create(['category_id' => $child->id, 'title' => 'Dokumen Tersembunyi Di Hub']);

        $this->get(route('arsip.show', $parent))
            ->assertOk()
            ->assertSee('Pilih sub-kategori')
            ->assertSee('RPS Hub')
            ->assertSee('1 dokumen')
            ->assertSee('Lihat semua 1 dokumen')
            ->assertDontSee('Dokumen Tersembunyi Di Hub');
    }

    public function test_hub_counts_respect_visitor_visibility(): void
    {
        $parent = Category::factory()->create();
        $child = Category::factory()->create(['parent_id' => $parent->id, 'name' => 'Sub Internal Saja']);
        Document::factory()->visibility(Document::VISIBILITY_INTERNAL)
            ->create(['category_id' => $child->id]);

        // Publik: sub tampak kosong
        $this->get(route('arsip.show', $parent))
            ->assertOk()
            ->assertSee('Belum ada dokumen');

        // Dosen: sub terhitung berisi
        $this->actingAs(User::factory()->dosen()->create());

        $this->get(route('arsip.show', $parent))
            ->assertOk()
            ->assertSee('1 dokumen');
    }

    public function test_leaf_category_still_lists_documents_directly(): void
    {
        $leaf = Category::factory()->create(['name' => 'Dokumentasi Tanpa Sub']);
        Document::factory()->create(['category_id' => $leaf->id, 'title' => 'Foto Kegiatan Prodi']);

        $this->get(route('arsip.show', $leaf))
            ->assertOk()
            ->assertSee('Foto Kegiatan Prodi')
            ->assertDontSee('Pilih sub-kategori');
    }

    public function test_hub_search_form_is_scoped_to_category(): void
    {
        $parent = Category::factory()->create(['slug' => 'arsip-akademik-hub']);
        Category::factory()->create(['parent_id' => $parent->id]);

        $this->get(route('arsip.show', $parent))
            ->assertOk()
            ->assertSee('name="kategori" value="arsip-akademik-hub"', false);
    }

    public function test_search_can_be_scoped_to_a_category(): void
    {
        $target = Category::factory()->create(['slug' => 'kategori-target']);
        $other = Category::factory()->create();

        Document::factory()->create(['category_id' => $target->id, 'title' => 'Panduan KP Target']);
        Document::factory()->create(['category_id' => $other->id, 'title' => 'Panduan KP Lainnya']);

        // Kata pendek memakai jalur LIKE (bukan FULLTEXT) — aman dalam transaksi test
        $this->get('/cari?q=KP&kategori=kategori-target')
            ->assertOk()
            ->assertSeeText('Panduan KP Target')
            ->assertDontSeeText('Panduan KP Lainnya')
            ->assertSeeText('di kategori');
    }
}

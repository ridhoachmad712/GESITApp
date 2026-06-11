<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchiveListViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_page_defaults_to_grid_and_can_switch_to_list(): void
    {
        $category = Category::factory()->create();
        Document::factory()->create([
            'category_id' => $category->id,
            'title' => 'Dokumen Uji Tampilan',
        ]);

        // Default: tampilan kartu, ada tombol toggle
        $this->get(route('arsip.show', $category))
            ->assertOk()
            ->assertSee('Dokumen Uji Tampilan')
            ->assertSee('Kartu')
            ->assertSee('Daftar');

        // Mode list tetap menampilkan dokumen + aksi Lihat/Unduh
        $this->get(route('arsip.show', ['category' => $category, 'tampilan' => 'list']))
            ->assertOk()
            ->assertSee('Dokumen Uji Tampilan')
            ->assertSee('Lihat')
            ->assertSee('Unduh');
    }

    public function test_year_filter_preserves_list_mode(): void
    {
        $category = Category::factory()->create();
        Document::factory()->create([
            'category_id' => $category->id,
            'academic_year' => '2025/2026',
        ]);

        $response = $this->get(route('arsip.show', [
            'category' => $category,
            'tampilan' => 'list',
        ]));

        // Form filter menyertakan hidden input tampilan=list
        $response->assertOk()
            ->assertSee('name="tampilan" value="list"', false);
    }
}

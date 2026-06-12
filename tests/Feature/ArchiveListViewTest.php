<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchiveListViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_page_defaults_to_list_and_can_switch_to_grid(): void
    {
        $category = Category::factory()->create();
        Document::factory()->create([
            'category_id' => $category->id,
            'title' => 'Dokumen Uji Tampilan',
        ]);

        // Default: tampilan daftar (baris dalam kontainer divide-y)
        $this->get(route('arsip.show', $category))
            ->assertOk()
            ->assertSee('Dokumen Uji Tampilan')
            ->assertSee('divide-y', false)
            ->assertSee('Kartu')
            ->assertSee('Daftar');

        // Mode kartu via ?tampilan=grid
        $this->get(route('arsip.show', ['category' => $category, 'tampilan' => 'grid']))
            ->assertOk()
            ->assertSee('Dokumen Uji Tampilan')
            ->assertDontSee('divide-y', false);
    }

    public function test_year_filter_preserves_grid_mode(): void
    {
        $category = Category::factory()->create();
        Document::factory()->create([
            'category_id' => $category->id,
            'academic_year' => '2025/2026',
        ]);

        // Form filter menyertakan hidden input tampilan=grid saat mode kartu
        $this->get(route('arsip.show', [
            'category' => $category,
            'tampilan' => 'grid',
        ]))
            ->assertOk()
            ->assertSee('name="tampilan" value="grid"', false);
    }
}

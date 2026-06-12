<?php

namespace Tests\Feature;

use App\Filament\Resources\CategoryResource\Pages\ListCategories;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminUxTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_global_search_field_is_available_in_topbar(): void
    {
        $this->get('/admin')
            ->assertOk()
            ->assertSee('fi-global-search', false);
    }

    public function test_admin_profile_page_is_available(): void
    {
        $this->get('/admin/profile')
            ->assertOk();
    }

    public function test_categories_can_be_reordered(): void
    {
        $first = Category::factory()->create(['sort_order' => 1]);
        $second = Category::factory()->create(['sort_order' => 2]);

        Livewire::test(ListCategories::class)
            ->call('reorderTable', [(string) $second->id, (string) $first->id]);

        $this->assertTrue(
            $second->fresh()->sort_order < $first->fresh()->sort_order,
            'Urutan kategori tidak berubah setelah reorder',
        );
    }
}

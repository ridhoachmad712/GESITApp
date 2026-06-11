<?php

namespace Tests\Feature;

use App\Filament\Resources\DocumentResource\Pages\CreateDocument;
use App\Models\Category;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DocumentCategoryPickerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('documents');
        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_document_can_be_created_under_a_subcategory(): void
    {
        $main = Category::factory()->create(['name' => 'Arsip Akademik Uji']);
        $child = Category::factory()->create(['parent_id' => $main->id, 'name' => 'RPS Uji']);

        Livewire::test(CreateDocument::class)
            ->fillForm([
                'title' => 'RPS Manajemen Operasional',
                'slug' => 'rps-manajemen-operasional',
                'kategori_utama' => $main->id,
                'category_id' => $child->id,
                'visibility' => Document::VISIBILITY_MAHASISWA,
                'status' => Document::STATUS_PUBLISHED,
                'file_path' => UploadedFile::fake()->create('rps.pdf', 60, 'application/pdf'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $document = Document::where('slug', 'rps-manajemen-operasional')->first();
        $this->assertNotNull($document);
        $this->assertSame($child->id, $document->category_id);
    }

    public function test_main_category_without_children_is_used_directly(): void
    {
        $main = Category::factory()->create(['name' => 'Dokumentasi Uji']);

        Livewire::test(CreateDocument::class)
            ->fillForm([
                'title' => 'Galeri Kegiatan',
                'slug' => 'galeri-kegiatan',
                'kategori_utama' => $main->id,
                'category_id' => $main->id,
                'visibility' => Document::VISIBILITY_PUBLIC,
                'status' => Document::STATUS_PUBLISHED,
                'file_path' => UploadedFile::fake()->create('galeri.pdf', 40, 'application/pdf'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame($main->id, Document::where('slug', 'galeri-kegiatan')->first()->category_id);
    }

    public function test_subcategory_of_other_main_category_is_rejected(): void
    {
        $mainA = Category::factory()->create();
        $mainB = Category::factory()->create();
        $childOfB = Category::factory()->create(['parent_id' => $mainB->id]);

        Livewire::test(CreateDocument::class)
            ->fillForm([
                'title' => 'Dokumen Salah Kategori',
                'slug' => 'dokumen-salah-kategori',
                'kategori_utama' => $mainA->id,
                'category_id' => $childOfB->id, // bukan sub dari mainA
                'visibility' => Document::VISIBILITY_PUBLIC,
                'status' => Document::STATUS_DRAFT,
                'file_path' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf'),
            ])
            ->call('create')
            ->assertHasFormErrors(['category_id']);

        $this->assertSame(0, Document::count());
    }
}

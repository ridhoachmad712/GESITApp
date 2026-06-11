<?php

namespace Tests\Feature;

use App\Filament\Pages\BulkUploadDocuments;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class BulkUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('documents');
    }

    public function test_admin_can_open_bulk_upload_page(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->get('/admin/bulk-upload-documents')
            ->assertOk()
            ->assertSee('Unggah Massal');
    }

    public function test_bulk_upload_creates_documents_with_shared_metadata(): void
    {
        $category = Category::factory()->create();
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        Livewire::test(BulkUploadDocuments::class)
            ->fillForm([
                'category_id' => $category->id,
                'visibility' => Document::VISIBILITY_MAHASISWA,
                'status' => Document::STATUS_PUBLISHED,
                'academic_year' => '2025/2026',
                'semester' => 'ganjil',
                'files' => [
                    UploadedFile::fake()->create('rps-manajemen-keuangan.pdf', 120, 'application/pdf'),
                    UploadedFile::fake()->create('rps-manajemen-keuangan.pdf', 90, 'application/pdf'),
                    UploadedFile::fake()->create('kontrak_kuliah_msdm.pdf', 80, 'application/pdf'),
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(3, Document::count());

        $first = Document::where('slug', 'rps-manajemen-keuangan')->first();
        $this->assertNotNull($first);
        $this->assertSame('Rps manajemen keuangan', $first->title);
        $this->assertSame(Document::VISIBILITY_MAHASISWA, $first->visibility);
        $this->assertSame(Document::STATUS_PUBLISHED, $first->status);
        $this->assertSame('2025/2026', $first->academic_year);
        $this->assertSame($admin->id, $first->uploaded_by);
        $this->assertTrue(Storage::disk('documents')->exists($first->file_path));

        // Nama file kembar mendapat slug unik
        $this->assertNotNull(Document::where('slug', 'rps-manajemen-keuangan-1')->first());

        // Setiap unggahan tercatat di activity log
        $this->assertSame(3, ActivityLog::where('action', ActivityLog::ACTION_UPLOAD)->count());
    }

    public function test_bulk_upload_rejects_disallowed_file_types(): void
    {
        $category = Category::factory()->create();
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(BulkUploadDocuments::class)
            ->fillForm([
                'category_id' => $category->id,
                'visibility' => Document::VISIBILITY_INTERNAL,
                'status' => Document::STATUS_DRAFT,
                'files' => [
                    UploadedFile::fake()->create('virus.exe', 10, 'application/x-msdownload'),
                ],
            ])
            ->call('save')
            ->assertHasErrors();

        $this->assertSame(0, Document::count());
    }
}

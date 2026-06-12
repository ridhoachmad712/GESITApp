<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DokumentasiGalleryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('documents');
    }

    private function makeImage(array $attributes = []): Document
    {
        $document = Document::factory()->create(array_merge([
            'mime_type' => 'image/jpeg',
        ], $attributes));

        Storage::disk('documents')->put($document->file_path, 'isi-gambar-jpeg');

        return $document;
    }

    public function test_gallery_shows_public_published_images_only(): void
    {
        $this->makeImage(['title' => 'Foto Seminar Prodi']);
        $this->makeImage(['title' => 'Foto Rapat Internal', 'visibility' => Document::VISIBILITY_INTERNAL]);
        Document::factory()->create(['title' => 'Dokumen PDF Biasa']); // bukan gambar

        $this->get('/dokumentasi')
            ->assertOk()
            ->assertSee('Foto Seminar Prodi')
            ->assertDontSee('Foto Rapat Internal')
            ->assertDontSee('Dokumen PDF Biasa');
    }

    public function test_gallery_year_filter_limits_results(): void
    {
        $this->makeImage(['title' => 'Foto Tahun Lama', 'academic_year' => '2024/2025']);
        $this->makeImage(['title' => 'Foto Tahun Baru', 'academic_year' => '2025/2026']);

        $this->get('/dokumentasi?tahun=2024/2025')
            ->assertOk()
            ->assertSee('Foto Tahun Lama')
            ->assertDontSee('Foto Tahun Baru');
    }

    public function test_image_route_serves_public_image_without_logging(): void
    {
        $photo = $this->makeImage();

        $this->get(route('documents.image', $photo))
            ->assertOk()
            ->assertHeader('Cache-Control', 'max-age=86400, public');

        // Tanpa log & tanpa counter — galeri tidak membanjiri activity_logs
        $this->assertSame(0, ActivityLog::count());
        $this->assertSame(0, $photo->fresh()->view_count);
    }

    public function test_image_route_rejects_non_public_or_non_image_documents(): void
    {
        $internal = $this->makeImage(['visibility' => Document::VISIBILITY_INTERNAL]);
        $draft = $this->makeImage(['status' => Document::STATUS_DRAFT]);
        $pdf = Document::factory()->create();
        Storage::disk('documents')->put($pdf->file_path, '%PDF-1.4');

        $this->get(route('documents.image', $internal))->assertNotFound();
        $this->get(route('documents.image', $draft))->assertNotFound();
        $this->get(route('documents.image', $pdf))->assertNotFound();
    }

    public function test_navbar_shows_dokumentasi_link(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Dokumentasi')
            ->assertSee(route('dokumentasi.index'), false);
    }
}

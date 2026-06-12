<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('downloads');
    }

    public function test_download_endpoint_is_rate_limited_to_30_per_minute(): void
    {
        Storage::fake('documents');

        $document = Document::factory()->create();
        Storage::disk('documents')->put($document->file_path, '%PDF-1.4 dummy');

        $user = User::factory()->mahasiswa()->create();
        $this->actingAs($user);

        for ($i = 0; $i < 30; $i++) {
            $this->get(route('documents.download', $document))->assertOk();
        }

        $this->get(route('documents.download', $document))
            ->assertStatus(429);
    }

    public function test_login_is_rate_limited_after_five_attempts(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => $user->email,
                'password' => 'salah-terus',
            ]);
        }

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'salah-terus',
        ]);

        // Pesan throttle auth, bukan pesan kredensial salah
        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString(
            'detik',
            session('errors')->first('email'),
        );
    }

    public function test_document_files_are_not_reachable_via_public_storage_url(): void
    {
        // File di disk privat `documents` (storage/app/documents) TIDAK boleh
        // terjangkau lewat symlink /storage (yang hanya memetakan storage/app/public).
        // Penanda konten sengaja beda dari nama file: halaman error menggema URL
        // permintaan (og:url), jadi nama file pasti muncul — isinya yang tidak boleh.
        Storage::disk('documents')->put('audit/rahasia-uji.pdf', 'ISI-FILE-TERLARANG-XYZ');

        $probes = [
            '/storage/documents/audit/rahasia-uji.pdf',
            '/storage/audit/rahasia-uji.pdf',
            '/documents/audit/rahasia-uji.pdf',
        ];

        try {
            foreach ($probes as $url) {
                $response = $this->get($url);

                // 404 (tidak ada route) atau 403 (route serve Laravel menolak
                // tanpa signed URL) — yang penting konten tidak pernah bocor.
                $this->assertGreaterThanOrEqual(400, $response->status(), "URL {$url} seharusnya ditolak");
                $this->assertStringNotContainsString('ISI-FILE-TERLARANG-XYZ', (string) $response->getContent(), "URL {$url} membocorkan isi file");
            }

            // Pastikan file memang ada secara fisik di luar webroot
            $this->assertFileExists(storage_path('app/documents/audit/rahasia-uji.pdf'));
            $this->assertFileDoesNotExist(public_path('storage/documents/audit/rahasia-uji.pdf'));
        } finally {
            Storage::disk('documents')->delete('audit/rahasia-uji.pdf');
        }
    }

    public function test_documents_disk_has_serving_disabled(): void
    {
        $this->assertFalse(config('filesystems.disks.documents.serve'));
    }
}

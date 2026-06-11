<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExternalDocumentTest extends TestCase
{
    use RefreshDatabase;

    private const DRIVE_URL = 'https://drive.google.com/file/d/1AbCdEfGhIjKlMnOpQrStUv/view';

    public function test_download_redirects_to_external_url_and_is_logged(): void
    {
        $document = Document::factory()->external(self::DRIVE_URL)->create();

        $this->get(route('documents.download', $document))
            ->assertRedirect(self::DRIVE_URL);

        $this->assertSame(1, $document->fresh()->download_count);
        $this->assertDatabaseHas('activity_logs', [
            'document_id' => $document->id,
            'action' => ActivityLog::ACTION_DOWNLOAD,
        ]);
    }

    public function test_preview_redirects_to_external_url(): void
    {
        $document = Document::factory()->external(self::DRIVE_URL)->create();

        $this->get(route('documents.preview', $document))
            ->assertRedirect(self::DRIVE_URL);

        $this->assertSame(1, $document->fresh()->view_count);
    }

    public function test_visibility_policy_still_guards_external_documents(): void
    {
        $document = Document::factory()
            ->external(self::DRIVE_URL)
            ->visibility(Document::VISIBILITY_INTERNAL)
            ->create();

        // Pengunjung publik → login dulu; mahasiswa → ditolak; URL tidak bocor
        $this->get(route('documents.download', $document))
            ->assertRedirect(route('login'));

        $this->actingAs(User::factory()->mahasiswa()->create());

        $response = $this->get(route('documents.download', $document));
        $response->assertForbidden();
        $this->assertStringNotContainsString(self::DRIVE_URL, (string) $response->getContent());

        // Dosen boleh
        $this->actingAs(User::factory()->dosen()->create());
        $this->get(route('documents.download', $document))
            ->assertRedirect(self::DRIVE_URL);
    }

    public function test_detail_page_embeds_google_drive_preview(): void
    {
        $document = Document::factory()->external(self::DRIVE_URL)->create();

        $this->get(route('documents.show', $document))
            ->assertOk()
            ->assertSee('https://drive.google.com/file/d/1AbCdEfGhIjKlMnOpQrStUv/preview', false)
            ->assertSee('Buka Dokumen');
    }

    public function test_detail_page_shows_open_button_for_non_drive_links(): void
    {
        $document = Document::factory()
            ->external('https://contoh-penyimpanan.unm.ac.id/dok/file-123')
            ->create();

        $this->get(route('documents.show', $document))
            ->assertOk()
            ->assertSee('penyimpanan eksternal')
            ->assertDontSee('drive.google.com/file/d/');
    }

    public function test_document_card_shows_buka_for_external_and_lihat_for_files(): void
    {
        Document::factory()->external(self::DRIVE_URL)->create(['title' => 'Dokumen Drive Eksternal']);
        Document::factory()->create(['title' => 'Dokumen File Lokal']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Buka')
            ->assertSee('Lihat')
            ->assertSee('Unduh');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_public_document_detail(): void
    {
        $document = Document::factory()->create(['title' => 'Kurikulum OBE 2025']);

        $this->get(route('documents.show', $document))
            ->assertOk()
            ->assertSee('Kurikulum OBE 2025')
            ->assertSee('Unduh Dokumen');
    }

    public function test_pdf_document_detail_contains_pdfjs_preview_container(): void
    {
        $document = Document::factory()->create();

        $this->get(route('documents.show', $document))
            ->assertOk()
            ->assertSee('pdf-container')
            ->assertSee(route('documents.preview', $document), false);
    }

    public function test_non_pdf_document_shows_no_preview_notice(): void
    {
        $document = Document::factory()->create([
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);

        $this->get(route('documents.show', $document))
            ->assertOk()
            ->assertSee('Pratinjau tidak tersedia');
    }

    public function test_guest_is_redirected_to_login_for_restricted_document(): void
    {
        $document = Document::factory()->visibility(Document::VISIBILITY_MAHASISWA)->create();

        $this->get(route('documents.show', $document))
            ->assertRedirect(route('login'));
    }

    public function test_mahasiswa_cannot_view_internal_document_detail(): void
    {
        $document = Document::factory()->visibility(Document::VISIBILITY_INTERNAL)->create();

        $this->actingAs(User::factory()->mahasiswa()->create());

        $this->get(route('documents.show', $document))->assertForbidden();
    }

    public function test_dosen_can_view_internal_document_detail(): void
    {
        $document = Document::factory()->visibility(Document::VISIBILITY_INTERNAL)->create();

        $this->actingAs(User::factory()->dosen()->create());

        $this->get(route('documents.show', $document))->assertOk();
    }
}

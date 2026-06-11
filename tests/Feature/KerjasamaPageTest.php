<?php

namespace Tests\Feature;

use App\Models\Agreement;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KerjasamaPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_kerjasama_page_shows_agreement_metadata(): void
    {
        Agreement::factory()->create([
            'title' => 'Kerja Sama Magang Industri',
            'partner_name' => 'PT Bank Sulselbar',
            'type' => 'MoU',
        ]);

        $this->get('/kerjasama')
            ->assertOk()
            ->assertSee('Kerja Sama Magang Industri')
            ->assertSee('PT Bank Sulselbar')
            ->assertSee('MoU')
            ->assertSee('Aktif');
    }

    public function test_expired_agreement_shows_kedaluwarsa_status(): void
    {
        Agreement::factory()->expired()->create([
            'title' => 'Kerja Sama Lama Berakhir',
        ]);

        $this->get('/kerjasama')
            ->assertOk()
            ->assertSee('Kerja Sama Lama Berakhir')
            ->assertSee('Kedaluwarsa');
    }

    public function test_kerjasama_page_never_links_agreement_files(): void
    {
        $document = Document::factory()
            ->visibility(Document::VISIBILITY_INTERNAL)
            ->create(['title' => 'File MoU Rahasia']);

        Agreement::factory()->create(['document_id' => $document->id]);

        $this->get('/kerjasama')
            ->assertOk()
            ->assertDontSee('File MoU Rahasia')
            ->assertDontSee(route('documents.download', $document), false)
            ->assertDontSee(route('documents.show', $document), false);
    }

    public function test_kerjasama_page_handles_empty_state(): void
    {
        $this->get('/kerjasama')
            ->assertOk()
            ->assertSee('Belum ada data kerja sama');
    }
}

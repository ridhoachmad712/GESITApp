<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_404_uses_custom_gesit_page(): void
    {
        $this->get('/halaman-yang-tidak-pernah-ada')
            ->assertNotFound()
            ->assertSee('Halaman Tidak Ditemukan')
            ->assertSee('Kembali ke Beranda');
    }

    public function test_403_uses_custom_gesit_page(): void
    {
        $document = Document::factory()->visibility(Document::VISIBILITY_INTERNAL)->create();

        $this->actingAs(User::factory()->mahasiswa()->create());

        $this->get(route('documents.show', $document))
            ->assertForbidden()
            ->assertSee('Akses Ditolak');
    }
}

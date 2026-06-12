<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_top_downloaded_documents(): void
    {
        Document::factory()->create(['title' => 'Panduan Paling Laris', 'download_count' => 120]);
        Document::factory()->create(['title' => 'Dokumen Tanpa Unduhan']);

        $this->actingAs(User::factory()->admin()->create());

        $this->get('/admin')
            ->assertOk()
            ->assertSee('Dokumen Terpopuler')
            ->assertSee('Panduan Paling Laris');
    }
}

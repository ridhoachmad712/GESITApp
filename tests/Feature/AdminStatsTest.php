<?php

namespace Tests\Feature;

use App\Filament\Widgets\ActivityTrendChart;
use App\Models\ActivityLog;
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
            ->assertSee('Panduan Paling Laris')
            ->assertSee('Tren Aktivitas 6 Bulan Terakhir');
    }

    public function test_activity_trend_chart_counts_downloads_and_views_per_month(): void
    {
        $document = Document::factory()->create();

        foreach (range(1, 3) as $i) {
            ActivityLog::create([
                'document_id' => $document->id,
                'action' => ActivityLog::ACTION_DOWNLOAD,
                'created_at' => now(),
            ]);
        }

        ActivityLog::create([
            'document_id' => $document->id,
            'action' => ActivityLog::ACTION_VIEW,
            'created_at' => now(),
        ]);

        // Unggahan tidak ikut dihitung di tren unduhan/dilihat
        ActivityLog::create([
            'document_id' => $document->id,
            'action' => ActivityLog::ACTION_UPLOAD,
            'created_at' => now(),
        ]);

        $this->actingAs(User::factory()->admin()->create());

        $widget = new ActivityTrendChart;
        $data = (new \ReflectionMethod($widget, 'getData'))->invoke($widget);

        $this->assertCount(6, $data['labels']);
        $this->assertSame(3, end($data['datasets'][0]['data'])); // unduhan bulan ini
        $this->assertSame(1, end($data['datasets'][1]['data'])); // dilihat bulan ini
    }
}

<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_activity_logs(): void
    {
        $user = User::factory()->mahasiswa()->create(['name' => 'Mahasiswa Pengunduh']);
        $document = Document::factory()->create(['title' => 'Dokumen Yang Diunduh']);

        ActivityLog::create([
            'user_id' => $user->id,
            'document_id' => $document->id,
            'action' => ActivityLog::ACTION_DOWNLOAD,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
        ]);

        $this->actingAs(User::factory()->admin()->create());

        $this->get('/admin/activity-logs')
            ->assertOk()
            ->assertSee('Log Aktivitas')
            ->assertSee('Mahasiswa Pengunduh')
            ->assertSee('Dokumen Yang Diunduh');
    }

    public function test_dashboard_shows_empty_categories_gap_analysis(): void
    {
        $emptyCategory = Category::factory()->create(['name' => 'Kategori Masih Kosong']);

        $filled = Category::factory()->create(['name' => 'Kategori Sudah Terisi']);
        Document::factory()->create(['category_id' => $filled->id]);

        $this->actingAs(User::factory()->admin()->create());

        $this->get('/admin')
            ->assertOk()
            ->assertSee('Gap Analysis')
            ->assertSee('Kategori Masih Kosong');

        $this->assertTrue($emptyCategory->documents()->doesntExist());
    }
}

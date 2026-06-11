<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DocumentAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('documents');
    }

    private function makeDocument(string $visibility, string $status = Document::STATUS_PUBLISHED): Document
    {
        $document = Document::factory()
            ->visibility($visibility)
            ->state(['status' => $status])
            ->create();

        Storage::disk('documents')->put($document->file_path, '%PDF-1.4 dummy');

        return $document;
    }

    private function actingAsRole(?string $role): ?User
    {
        if ($role === null) {
            return null;
        }

        $user = User::factory()->{$role}()->create();
        $this->actingAs($user);

        return $user;
    }

    /**
     * Matriks 12 kasus: 4 role (publik, mahasiswa, dosen, admin) × 3 visibility.
     * 'ok' = boleh akses, 'login' = diarahkan ke login, 'forbidden' = 403.
     */
    public static function accessMatrix(): array
    {
        return [
            'publik → dokumen public' => [null, Document::VISIBILITY_PUBLIC, 'ok'],
            'publik → dokumen mahasiswa' => [null, Document::VISIBILITY_MAHASISWA, 'login'],
            'publik → dokumen internal' => [null, Document::VISIBILITY_INTERNAL, 'login'],
            'mahasiswa → dokumen public' => ['mahasiswa', Document::VISIBILITY_PUBLIC, 'ok'],
            'mahasiswa → dokumen mahasiswa' => ['mahasiswa', Document::VISIBILITY_MAHASISWA, 'ok'],
            'mahasiswa → dokumen internal' => ['mahasiswa', Document::VISIBILITY_INTERNAL, 'forbidden'],
            'dosen → dokumen public' => ['dosen', Document::VISIBILITY_PUBLIC, 'ok'],
            'dosen → dokumen mahasiswa' => ['dosen', Document::VISIBILITY_MAHASISWA, 'ok'],
            'dosen → dokumen internal' => ['dosen', Document::VISIBILITY_INTERNAL, 'ok'],
            'admin → dokumen public' => ['admin', Document::VISIBILITY_PUBLIC, 'ok'],
            'admin → dokumen mahasiswa' => ['admin', Document::VISIBILITY_MAHASISWA, 'ok'],
            'admin → dokumen internal' => ['admin', Document::VISIBILITY_INTERNAL, 'ok'],
        ];
    }

    #[DataProvider('accessMatrix')]
    public function test_download_respects_visibility_hierarchy(?string $role, string $visibility, string $expected): void
    {
        $document = $this->makeDocument($visibility);
        $this->actingAsRole($role);

        $response = $this->get(route('documents.download', $document));

        match ($expected) {
            'ok' => $response->assertOk()
                ->assertDownload($document->file_name),
            'login' => $response->assertRedirect(route('login')),
            'forbidden' => $response->assertForbidden(),
        };
    }

    #[DataProvider('accessMatrix')]
    public function test_preview_respects_visibility_hierarchy(?string $role, string $visibility, string $expected): void
    {
        $document = $this->makeDocument($visibility);
        $this->actingAsRole($role);

        $response = $this->get(route('documents.preview', $document));

        match ($expected) {
            'ok' => $response->assertOk(),
            'login' => $response->assertRedirect(route('login')),
            'forbidden' => $response->assertForbidden(),
        };
    }

    public function test_download_is_logged_and_counted(): void
    {
        $document = $this->makeDocument(Document::VISIBILITY_MAHASISWA);
        $user = $this->actingAsRole('mahasiswa');

        $this->get(route('documents.download', $document))->assertOk();

        $this->assertSame(1, $document->fresh()->download_count);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'document_id' => $document->id,
            'action' => ActivityLog::ACTION_DOWNLOAD,
        ]);
    }

    public function test_anonymous_preview_is_logged_without_user(): void
    {
        $document = $this->makeDocument(Document::VISIBILITY_PUBLIC);

        $this->get(route('documents.preview', $document))->assertOk();

        $this->assertSame(1, $document->fresh()->view_count);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => null,
            'document_id' => $document->id,
            'action' => ActivityLog::ACTION_VIEW,
        ]);
    }

    public function test_denied_access_is_not_logged_and_not_counted(): void
    {
        $document = $this->makeDocument(Document::VISIBILITY_INTERNAL);
        $this->actingAsRole('mahasiswa');

        $this->get(route('documents.download', $document))->assertForbidden();

        $this->assertSame(0, $document->fresh()->download_count);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_draft_document_is_hidden_except_for_admin(): void
    {
        $document = $this->makeDocument(Document::VISIBILITY_PUBLIC, Document::STATUS_DRAFT);

        $this->actingAsRole('dosen');
        $this->get(route('documents.download', $document))->assertForbidden();

        auth()->logout();

        $this->actingAsRole('admin');
        $this->get(route('documents.download', $document))->assertOk();
    }

    public function test_soft_deleted_document_returns_404(): void
    {
        $document = $this->makeDocument(Document::VISIBILITY_PUBLIC);
        $document->delete();

        $this->actingAsRole('admin');

        $this->get(route('documents.download', $document->slug))->assertNotFound();
    }

    public function test_inactive_user_is_treated_as_unauthorized(): void
    {
        $document = $this->makeDocument(Document::VISIBILITY_INTERNAL);

        $user = User::factory()->dosen()->inactive()->create();
        $this->actingAs($user);

        $this->get(route('documents.download', $document))->assertForbidden();
    }

    public function test_missing_file_returns_404(): void
    {
        $document = Document::factory()->create(); // file tidak pernah ditulis ke disk

        $this->get(route('documents.download', $document))->assertNotFound();
    }
}

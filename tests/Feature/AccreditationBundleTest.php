<?php

namespace Tests\Feature;

use App\Filament\Resources\DocumentResource\Pages\CreateDocument;
use App\Models\AccreditationCriterion;
use App\Models\Category;
use App\Models\Document;
use App\Models\User;
use Database\Seeders\AccreditationCriteriaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AccreditationBundleTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_nine_lamemba_criteria(): void
    {
        $this->seed(AccreditationCriteriaSeeder::class);

        $this->assertSame(9, AccreditationCriterion::count());
        $this->assertSame('Visi, Misi, Tujuan, dan Strategi', AccreditationCriterion::where('code', 'K1')->first()->name);
    }

    public function test_bundle_page_requires_dosen_or_admin(): void
    {
        $this->get('/bundel-akreditasi')->assertRedirect(route('login'));

        $this->actingAs(User::factory()->mahasiswa()->create());
        $this->get('/bundel-akreditasi')->assertForbidden();
    }

    public function test_bundle_groups_evidence_per_criterion(): void
    {
        $k1 = AccreditationCriterion::factory()->create(['code' => 'K1', 'name' => 'Kriteria Visi Uji']);
        $k2 = AccreditationCriterion::factory()->create(['code' => 'K2', 'name' => 'Kriteria Tata Kelola Uji']);

        $evidence = Document::factory()->visibility(Document::VISIBILITY_INTERNAL)
            ->create(['title' => 'LKPS Bukti Kinerja 2025']);
        $evidence->criteria()->attach($k1);

        $this->actingAs(User::factory()->dosen()->create());

        $this->get('/bundel-akreditasi')
            ->assertOk()
            ->assertSee('Kriteria Visi Uji')
            ->assertSee('LKPS Bukti Kinerja 2025')
            ->assertSee('Kriteria Tata Kelola Uji')
            ->assertSee('Belum ada bukti');
    }

    public function test_draft_documents_are_excluded_from_bundle(): void
    {
        $k1 = AccreditationCriterion::factory()->create();
        $draft = Document::factory()->draft()->create(['title' => 'Draf Bukan Bukti']);
        $draft->criteria()->attach($k1);

        $this->actingAs(User::factory()->admin()->create());

        $this->get('/bundel-akreditasi')
            ->assertOk()
            ->assertDontSee('Draf Bukan Bukti');
    }

    public function test_document_can_be_tagged_with_criteria_via_admin_form(): void
    {
        Storage::fake('documents');
        $this->actingAs(User::factory()->admin()->create());

        $category = Category::factory()->create();
        $k6 = AccreditationCriterion::factory()->create(['code' => 'K6']);

        Livewire::test(CreateDocument::class)
            ->fillForm([
                'title' => 'RPS Bukti Pendidikan',
                'slug' => 'rps-bukti-pendidikan',
                'kategori_utama' => $category->id,
                'category_id' => $category->id,
                'visibility' => Document::VISIBILITY_INTERNAL,
                'status' => Document::STATUS_PUBLISHED,
                'criteria' => [$k6->id],
                'file_path' => UploadedFile::fake()->create('rps.pdf', 40, 'application/pdf'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $document = Document::where('slug', 'rps-bukti-pendidikan')->first();
        $this->assertTrue($document->criteria->contains($k6));
    }

    public function test_detail_page_shows_criteria_badges(): void
    {
        $k1 = AccreditationCriterion::factory()->create(['code' => 'K1']);
        $document = Document::factory()->create();
        $document->criteria()->attach($k1);

        $this->get(route('documents.show', $document))
            ->assertOk()
            ->assertSee('Bukti kriteria akreditasi')
            ->assertSee('K1');
    }

    public function test_admin_can_open_criteria_resource(): void
    {
        $this->seed(AccreditationCriteriaSeeder::class);
        $this->actingAs(User::factory()->admin()->create());

        $this->get('/admin/accreditation-criteria')
            ->assertOk()
            ->assertSee('Kriteria Akreditasi')
            ->assertSee('K9');
    }

    public function test_dosen_nav_shows_bundle_link_but_mahasiswa_does_not(): void
    {
        $this->actingAs(User::factory()->dosen()->create());
        $this->get('/dashboard')->assertSee('Bundel Akreditasi');

        $this->actingAs(User::factory()->mahasiswa()->create());
        $this->get('/dashboard')->assertDontSee('Bundel Akreditasi');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_mahasiswa_sees_public_and_mahasiswa_documents_only(): void
    {
        Document::factory()->create(['title' => 'Dokumen Publik Dasbor']);
        Document::factory()->visibility(Document::VISIBILITY_MAHASISWA)
            ->create(['title' => 'Modul Mahasiswa Dasbor']);
        Document::factory()->visibility(Document::VISIBILITY_INTERNAL)
            ->create(['title' => 'Notulen Internal Dasbor']);
        Document::factory()->draft()->create(['title' => 'Draf Dasbor']);

        $this->actingAs(User::factory()->mahasiswa()->create());

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Dokumen Publik Dasbor')
            ->assertSee('Modul Mahasiswa Dasbor')
            ->assertDontSee('Notulen Internal Dasbor')
            ->assertDontSee('Draf Dasbor');
    }

    public function test_dosen_sees_internal_documents(): void
    {
        Document::factory()->visibility(Document::VISIBILITY_INTERNAL)
            ->create(['title' => 'Notulen Internal Dasbor']);

        $this->actingAs(User::factory()->dosen()->create());

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Notulen Internal Dasbor');
    }

    public function test_category_filter_limits_results(): void
    {
        $categoryA = Category::factory()->create();
        $categoryB = Category::factory()->create();

        Document::factory()->create(['category_id' => $categoryA->id, 'title' => 'Dokumen Kategori A']);
        Document::factory()->create(['category_id' => $categoryB->id, 'title' => 'Dokumen Kategori B']);

        $this->actingAs(User::factory()->mahasiswa()->create());

        $this->get('/dashboard?kategori='.$categoryA->id)
            ->assertOk()
            ->assertSee('Dokumen Kategori A')
            ->assertDontSee('Dokumen Kategori B');
    }

    public function test_year_and_semester_filters_limit_results(): void
    {
        Document::factory()->create([
            'title' => 'RPS Ganjil Lama',
            'academic_year' => '2024/2025',
            'semester' => 'ganjil',
        ]);
        Document::factory()->create([
            'title' => 'RPS Genap Baru',
            'academic_year' => '2025/2026',
            'semester' => 'genap',
        ]);

        $this->actingAs(User::factory()->mahasiswa()->create());

        $this->get('/dashboard?tahun=2025/2026&semester=genap')
            ->assertOk()
            ->assertSee('RPS Genap Baru')
            ->assertDontSee('RPS Ganjil Lama');
    }

    public function test_search_matches_title_and_course_name(): void
    {
        Document::factory()->create(['title' => 'Modul Perkuliahan Umum', 'course_name' => 'Manajemen Strategik']);
        Document::factory()->create(['title' => 'Dokumen Lain Sama Sekali']);

        $this->actingAs(User::factory()->mahasiswa()->create());

        $this->get('/dashboard?q=strategik')
            ->assertOk()
            ->assertSee('Modul Perkuliahan Umum')
            ->assertDontSee('Dokumen Lain Sama Sekali');
    }
}

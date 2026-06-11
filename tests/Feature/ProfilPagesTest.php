<?php

namespace Tests\Feature;

use App\Models\Lecturer;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfilPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_profil_index_lists_static_pages_and_lecturer_link(): void
    {
        $this->seed(PageSeeder::class);

        $this->get('/profil')
            ->assertOk()
            ->assertSee('Sejarah Program Studi')
            ->assertSee('Visi, Misi, Tujuan, dan Strategi')
            ->assertSee('Struktur Organisasi')
            ->assertSee('Dosen Program Studi');
    }

    public function test_static_pages_render_their_content(): void
    {
        $this->seed(PageSeeder::class);

        $this->get('/profil/sejarah')
            ->assertOk()
            ->assertSee('Sejarah Program Studi');

        $this->get('/profil/visi-misi')
            ->assertOk()
            ->assertSee('Visi');

        $this->get('/profil/struktur-organisasi')
            ->assertOk()
            ->assertSee('Struktur Organisasi');
    }

    public function test_unknown_static_page_returns_404(): void
    {
        $this->get('/profil/halaman-tidak-ada')->assertNotFound();
    }

    public function test_dosen_page_shows_active_lecturers_only(): void
    {
        Lecturer::factory()->create([
            'name' => 'Dr. Andi Caraka, M.M.',
            'nidn' => '0011223344',
            'expertise' => 'Manajemen Keuangan',
        ]);
        Lecturer::factory()->create([
            'name' => 'Dr. Dosen Nonaktif, M.M.',
            'is_active' => false,
        ]);

        $this->get('/profil/dosen')
            ->assertOk()
            ->assertSee('Dr. Andi Caraka, M.M.')
            ->assertSee('0011223344')
            ->assertSee('Manajemen Keuangan')
            ->assertSee('Lihat Publikasi')
            ->assertDontSee('Dr. Dosen Nonaktif, M.M.');
    }

    public function test_dosen_page_handles_empty_state(): void
    {
        $this->get('/profil/dosen')
            ->assertOk()
            ->assertSee('Data dosen belum tersedia');
    }
}

<?php

namespace Tests\Feature;

use App\Filament\Pages\ImportUsers;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ImportUsersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_admin_can_open_import_page(): void
    {
        $this->get('/admin/import-users')
            ->assertOk()
            ->assertSee('Impor Pengguna');
    }

    public function test_csv_import_creates_active_users_with_identity_password(): void
    {
        $csv = implode("\n", [
            'nama,email,nim',
            'Budi Santoso,budi@student.unm.ac.id,210901501001',
            'Sinta Dewi,sinta@student.unm.ac.id,210901501002',
        ]);

        Livewire::test(ImportUsers::class)
            ->fillForm([
                'file' => UploadedFile::fake()->createWithContent('mahasiswa.csv', $csv),
                'role' => User::ROLE_MAHASISWA,
                'password_mode' => 'identity',
            ])
            ->call('import')
            ->assertHasNoFormErrors();

        $budi = User::where('email', 'budi@student.unm.ac.id')->first();
        $this->assertNotNull($budi);
        $this->assertSame(User::ROLE_MAHASISWA, $budi->role);
        $this->assertSame('210901501001', $budi->identity_number);
        $this->assertTrue($budi->is_active);
        $this->assertTrue(Hash::check('210901501001', $budi->password));

        $this->assertNotNull(User::where('email', 'sinta@student.unm.ac.id')->first());
    }

    public function test_semicolon_csv_with_alias_headers_and_fixed_password(): void
    {
        $csv = implode("\n", [
            'NIP;Nama Lengkap;E-mail',
            '198001012005011001;Dr. Rahmat Hidayat;rahmat@unm.ac.id',
        ]);

        Livewire::test(ImportUsers::class)
            ->fillForm([
                'file' => UploadedFile::fake()->createWithContent('dosen.csv', $csv),
                'role' => User::ROLE_DOSEN,
                'password_mode' => 'fixed',
                'fixed_password' => 'GesitDosen#2026',
            ])
            ->call('import')
            ->assertHasNoFormErrors();

        $dosen = User::where('email', 'rahmat@unm.ac.id')->first();
        $this->assertNotNull($dosen);
        $this->assertSame(User::ROLE_DOSEN, $dosen->role);
        $this->assertTrue(Hash::check('GesitDosen#2026', $dosen->password));
    }

    public function test_invalid_and_duplicate_rows_are_skipped_with_reasons(): void
    {
        User::factory()->create(['email' => 'sudah@unm.ac.id', 'identity_number' => '999']);

        $csv = implode("\n", [
            'nama,email,nim',
            'Valid Mahasiswa,valid@unm.ac.id,111',
            ',tanpanama@unm.ac.id,222',          // nama kosong
            'Email Rusak,bukan-email,333',        // email tidak valid
            'Duplikat Email,sudah@unm.ac.id,444', // email sudah ada
            'Duplikat Nim,baru@unm.ac.id,999',    // NIM sudah ada
            'Valid Mahasiswa,valid@unm.ac.id,555', // duplikat dalam file
        ]);

        $component = Livewire::test(ImportUsers::class)
            ->fillForm([
                'file' => UploadedFile::fake()->createWithContent('campur.csv', $csv),
                'role' => User::ROLE_MAHASISWA,
                'password_mode' => 'identity',
            ])
            ->call('import');

        $result = $component->get('importResult');

        $this->assertSame(1, $result['created']);
        $this->assertCount(5, $result['skipped']);
        $this->assertNotNull(User::where('email', 'valid@unm.ac.id')->first());
        // 1 admin + 1 existing + 1 imported
        $this->assertSame(3, User::count());
    }

    public function test_unrecognized_header_is_reported(): void
    {
        $csv = "kolom_a,kolom_b\nisi,isi";

        $component = Livewire::test(ImportUsers::class)
            ->fillForm([
                'file' => UploadedFile::fake()->createWithContent('salah.csv', $csv),
                'role' => User::ROLE_MAHASISWA,
                'password_mode' => 'identity',
            ])
            ->call('import');

        $result = $component->get('importResult');

        $this->assertSame(0, $result['created']);
        $this->assertStringContainsString('Baris judul tidak dikenali', $result['skipped'][0]['reason']);
    }

    public function test_non_admin_cannot_open_import_page(): void
    {
        $this->actingAs(User::factory()->dosen()->create());

        $this->get('/admin/import-users')->assertForbidden();
    }
}

<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Services\UserImporter;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

class ImportUsers extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Impor Pengguna';

    protected static ?string $navigationLabel = 'Impor Pengguna';

    protected static string $view = 'filament.pages.import-users';

    /** @var array<string, mixed> */
    public ?array $data = [];

    /** @var array{created: int, skipped: array<int, array{row: int, reason: string}>}|null */
    public ?array $importResult = null;

    public function mount(): void
    {
        $this->form->fill([
            'role' => User::ROLE_MAHASISWA,
            'password_mode' => 'identity',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Forms\Components\Section::make('Berkas Daftar Pengguna')
                    ->description('Format CSV atau Excel (.xlsx). Baris pertama wajib berisi judul kolom: nama, email, dan nim/nip/nomor_identitas (urutan bebas, maksimal 1.000 baris).')
                    ->schema([
                        Forms\Components\FileUpload::make('file')
                            ->label('Berkas CSV / XLSX')
                            ->disk('local')
                            ->directory('imports')
                            ->acceptedFileTypes([
                                'text/csv',
                                'text/plain',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            ])
                            ->maxSize(5120)
                            ->required(),
                    ]),

                Forms\Components\Section::make('Pengaturan Akun')
                    ->schema([
                        Forms\Components\Select::make('role')
                            ->label('Role untuk semua baris')
                            ->options([
                                User::ROLE_MAHASISWA => 'Mahasiswa',
                                User::ROLE_DOSEN => 'Dosen',
                            ])
                            ->required()
                            ->helperText('Impor mahasiswa dan dosen secara terpisah. Akun admin dibuat manual di menu Pengguna.'),
                        Forms\Components\Radio::make('password_mode')
                            ->label('Password awal')
                            ->options([
                                'identity' => 'Pakai nomor identitas (NIM/NIP) masing-masing',
                                'fixed' => 'Satu password sama untuk semua akun',
                            ])
                            ->required()
                            ->live(),
                        Forms\Components\TextInput::make('fixed_password')
                            ->label('Password untuk semua akun')
                            ->password()
                            ->revealable()
                            ->minLength(8)
                            ->visible(fn (Get $get): bool => $get('password_mode') === 'fixed')
                            ->required(fn (Get $get): bool => $get('password_mode') === 'fixed'),
                    ])
                    ->columns(1),
            ]);
    }

    public function import(UserImporter $importer): void
    {
        $data = $this->form->getState();

        $path = Storage::disk('local')->path($data['file']);

        $this->importResult = $importer->import(
            $path,
            $data['role'],
            $data['password_mode'],
            $data['fixed_password'] ?? null,
        );

        Storage::disk('local')->delete($data['file']);

        // Akun langsung aktif — semua pengguna sebaiknya segera ganti password
        $this->form->fill([
            'role' => $data['role'],
            'password_mode' => $data['password_mode'],
        ]);

        $skippedCount = count($this->importResult['skipped']);

        Notification::make()
            ->{$this->importResult['created'] > 0 ? 'success' : 'warning'}()
            ->title($this->importResult['created'].' akun berhasil dibuat')
            ->body($skippedCount > 0 ? "{$skippedCount} baris dilewati — rincian di bawah formulir." : 'Semua baris berhasil diproses.')
            ->send();
    }
}

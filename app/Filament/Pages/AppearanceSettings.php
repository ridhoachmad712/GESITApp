<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class AppearanceSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Pengaturan Tampilan';

    protected static ?string $navigationLabel = 'Pengaturan Tampilan';

    protected static string $view = 'filament.pages.appearance-settings';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'site_name' => Setting::get('site_name'),
            'site_tagline' => Setting::get('site_tagline'),
            'site_owner' => Setting::get('site_owner'),
            'primary_color' => Setting::get('primary_color'),
            'logo_path' => Setting::get('logo_path'),
            'hero_image_path' => Setting::get('hero_image_path'),
            'hero_overlay_color' => Setting::get('hero_overlay_color'),
            'hero_overlay_opacity' => Setting::get('hero_overlay_opacity'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Forms\Components\Section::make('Identitas Situs')
                    ->schema([
                        Forms\Components\TextInput::make('site_name')
                            ->label('Nama situs')
                            ->required()
                            ->maxLength(50)
                            ->helperText('Tampil di navbar, judul tab browser, dan footer.'),
                        Forms\Components\TextInput::make('site_tagline')
                            ->label('Tagline / kepanjangan nama')
                            ->required()
                            ->maxLength(120),
                        Forms\Components\TextInput::make('site_owner')
                            ->label('Identitas pemilik')
                            ->required()
                            ->maxLength(120)
                            ->helperText('Mis. nama program studi/fakultas. Tampil di bawah nama situs dan hak cipta footer.'),
                        Forms\Components\FileUpload::make('logo_path')
                            ->label('Logo')
                            ->image()
                            ->disk('public')
                            ->directory('logo')
                            ->maxSize(1024)
                            ->helperText('Opsional, PNG/JPG/SVG maks 1 MB. Jika kosong, dipakai kotak inisial nama situs.'),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Warna')
                    ->schema([
                        Forms\Components\ColorPicker::make('primary_color')
                            ->label('Warna dasar')
                            ->required()
                            ->regex('/^#[0-9a-fA-F]{6}$/')
                            ->helperText('Berlaku untuk situs publik dan panel admin. Gradasi terang–gelap dihitung otomatis. Tidak perlu build ulang.'),
                    ]),

                Forms\Components\Section::make('Hero Beranda')
                    ->description('Latar belakang area judul besar di halaman depan. Tanpa foto, hero memakai gradasi warna dasar.')
                    ->schema([
                        Forms\Components\FileUpload::make('hero_image_path')
                            ->label('Foto latar hero')
                            ->image()
                            ->disk('public')
                            ->directory('hero')
                            ->maxSize(3072)
                            ->helperText('Opsional, JPG/PNG maks 3 MB. Disarankan foto lanskap minimal 1600px, mis. gedung kampus atau kegiatan prodi.'),
                        Forms\Components\ColorPicker::make('hero_overlay_color')
                            ->label('Warna overlay')
                            ->required()
                            ->regex('/^#[0-9a-fA-F]{6}$/')
                            ->helperText('Lapisan warna di atas foto agar teks tetap terbaca.'),
                        Forms\Components\TextInput::make('hero_overlay_opacity')
                            ->label('Ketebalan overlay')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->required()
                            ->suffix('%')
                            ->helperText('0 = foto polos tanpa overlay, 100 = warna penuh (foto tidak terlihat). Disarankan 60–85.'),
                    ])
                    ->columns(2),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach (['site_name', 'site_tagline', 'site_owner', 'primary_color', 'hero_overlay_color', 'hero_overlay_opacity'] as $key) {
            Setting::set($key, $data[$key]);
        }

        Setting::set('logo_path', $data['logo_path'] ?: null);
        Setting::set('hero_image_path', $data['hero_image_path'] ?: null);

        Notification::make()
            ->success()
            ->title('Pengaturan tersimpan')
            ->body('Perubahan langsung berlaku di seluruh situs.')
            ->send();
    }

    protected function getFormActions(): array
    {
        return [];
    }
}

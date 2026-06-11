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
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach (['site_name', 'site_tagline', 'site_owner', 'primary_color'] as $key) {
            Setting::set($key, $data[$key]);
        }

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

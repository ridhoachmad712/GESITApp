<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class HomepageContent extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Konten Beranda';

    protected static ?string $navigationLabel = 'Konten Beranda';

    protected static string $view = 'filament.pages.homepage-content';

    /** Key teks biasa yang disimpan apa adanya. */
    private const TEXT_KEYS = [
        'hero_title',
        'hero_description',
        'hero_search_placeholder',
        'login_banner_text',
        'login_banner_button',
        'announcement_text',
        'announcement_link_label',
        'announcement_link_url',
        'section_featured_title',
        'section_featured_subtitle',
        'section_featured_order',
        'section_categories_title',
        'section_categories_subtitle',
        'section_categories_order',
        'section_popular_title',
        'section_popular_subtitle',
        'section_popular_order',
        'section_latest_title',
        'section_latest_subtitle',
        'section_latest_order',
        'nav_beranda_order',
        'nav_arsip_order',
        'nav_cari_order',
        'footer_contact_line1',
        'footer_contact_line2',
        'footer_link_label',
        'footer_link_url',
    ];

    /** Key bertipe boolean (toggle), disimpan '1'/'0'. */
    private const TOGGLE_KEYS = [
        'announcement_enabled',
        'login_banner_enabled',
        'section_featured_visible',
        'section_categories_visible',
        'section_popular_visible',
        'section_latest_visible',
    ];

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $state = [];

        foreach (self::TEXT_KEYS as $key) {
            $state[$key] = Setting::get($key);
        }

        foreach (self::TOGGLE_KEYS as $key) {
            $state[$key] = Setting::get($key) === '1';
        }

        $state['hero_search_chips'] = json_decode(Setting::get('hero_search_chips') ?? '[]', true) ?: [];

        $this->form->fill($state);
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Forms\Components\Section::make('Hero (Area Judul Besar)')
                    ->schema([
                        Forms\Components\TextInput::make('hero_title')
                            ->label('Judul hero')
                            ->maxLength(120)
                            ->placeholder(Setting::get('site_name').' — '.Setting::get('site_tagline'))
                            ->helperText('Kosongkan untuk otomatis memakai "Nama situs — Tagline" dari Pengaturan Tampilan.'),
                        Forms\Components\Textarea::make('hero_description')
                            ->label('Deskripsi hero')
                            ->rows(2)
                            ->required()
                            ->maxLength(300),
                        Forms\Components\TextInput::make('hero_search_placeholder')
                            ->label('Teks placeholder kotak pencarian')
                            ->required()
                            ->maxLength(80),
                        Forms\Components\TagsInput::make('hero_search_chips')
                            ->label('Chip pencarian populer')
                            ->placeholder('Ketik kata kunci lalu Enter')
                            ->helperText('Tampil di bawah kotak pencarian; klik = langsung mencari. Kosongkan semua untuk menyembunyikan.'),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Bar Pengumuman')
                    ->description('Strip tipis di paling atas SEMUA halaman publik — untuk info musiman (pengisian KRS, jadwal maintenance, dsb.).')
                    ->schema([
                        Forms\Components\Toggle::make('announcement_enabled')
                            ->label('Tampilkan pengumuman'),
                        Forms\Components\TextInput::make('announcement_text')
                            ->label('Teks pengumuman')
                            ->maxLength(160)
                            ->requiredIfAccepted('announcement_enabled'),
                        Forms\Components\TextInput::make('announcement_link_label')
                            ->label('Label tautan (opsional)')
                            ->maxLength(40),
                        Forms\Components\TextInput::make('announcement_link_url')
                            ->label('URL tautan (opsional)')
                            ->url()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Banner Ajakan Masuk')
                    ->schema([
                        Forms\Components\Toggle::make('login_banner_enabled')
                            ->label('Tampilkan banner (hanya untuk pengunjung belum login)'),
                        Forms\Components\TextInput::make('login_banner_text')
                            ->label('Teks banner')
                            ->required()
                            ->maxLength(200),
                        Forms\Components\TextInput::make('login_banner_button')
                            ->label('Label tombol')
                            ->required()
                            ->maxLength(30),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Seksi Beranda')
                    ->description('Atur judul, sub-judul, urutan tampil (angka kecil lebih dulu), dan tampil/sembunyi tiap seksi. Seksi Unggulan dan Paling Banyak Diunduh otomatis tersembunyi bila datanya kosong.')
                    ->schema([
                        self::sectionFieldset('featured', 'Dokumen Unggulan'),
                        self::sectionFieldset('categories', 'Kategori Arsip', 'Gunakan {jumlah} pada sub-judul untuk menampilkan jumlah kategori.'),
                        self::sectionFieldset('popular', 'Paling Banyak Diunduh'),
                        self::sectionFieldset('latest', 'Dokumen Terbaru'),
                    ]),

                Forms\Components\Section::make('Urutan Menu Navigasi')
                    ->description('Angka kecil tampil lebih dulu di navbar.')
                    ->schema([
                        Forms\Components\TextInput::make('nav_beranda_order')
                            ->label('Beranda')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('nav_arsip_order')
                            ->label('Arsip')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('nav_cari_order')
                            ->label('Pencarian')
                            ->numeric()
                            ->required(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Footer — Kontak')
                    ->schema([
                        Forms\Components\TextInput::make('footer_contact_line1')
                            ->label('Baris alamat 1')
                            ->maxLength(120),
                        Forms\Components\TextInput::make('footer_contact_line2')
                            ->label('Baris alamat 2')
                            ->maxLength(120),
                        Forms\Components\TextInput::make('footer_link_label')
                            ->label('Label tautan situs')
                            ->maxLength(60),
                        Forms\Components\TextInput::make('footer_link_url')
                            ->label('URL tautan situs')
                            ->url()
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach (self::TEXT_KEYS as $key) {
            Setting::set($key, $data[$key] ?? null);
        }

        foreach (self::TOGGLE_KEYS as $key) {
            Setting::set($key, ($data[$key] ?? false) ? '1' : '0');
        }

        Setting::set('hero_search_chips', json_encode(array_values($data['hero_search_chips'] ?? [])));

        Notification::make()
            ->success()
            ->title('Konten beranda tersimpan')
            ->body('Perubahan langsung tampil di halaman depan.')
            ->send();
    }

    private static function sectionFieldset(string $key, string $label, ?string $subtitleHelper = null): Forms\Components\Fieldset
    {
        return Forms\Components\Fieldset::make($label)
            ->schema([
                Forms\Components\TextInput::make("section_{$key}_title")
                    ->label('Judul')
                    ->required()
                    ->maxLength(80)
                    ->columnSpan(2),
                Forms\Components\TextInput::make("section_{$key}_subtitle")
                    ->label('Sub-judul (opsional)')
                    ->maxLength(160)
                    ->helperText($subtitleHelper)
                    ->columnSpan(2),
                Forms\Components\TextInput::make("section_{$key}_order")
                    ->label('Urutan')
                    ->numeric()
                    ->required(),
                Forms\Components\Toggle::make("section_{$key}_visible")
                    ->label('Tampilkan')
                    ->inline(false),
            ])
            ->columns(6);
    }
}

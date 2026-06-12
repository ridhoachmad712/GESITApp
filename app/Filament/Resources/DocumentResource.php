<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use App\Models\Category;
use App\Models\Document;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Manajemen Arsip';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Dokumen';

    protected static ?string $pluralModelLabel = 'Dokumen';

    protected static ?string $recordTitleAttribute = 'title';

    /**
     * Pencarian global (topbar panel admin).
     *
     * @return array<string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'course_name', 'lecturer_name'];
    }

    /**
     * @return array<string, string>
     */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Kategori' => $record->category?->name ?? '—',
            'Status' => match ($record->status) {
                Document::STATUS_PUBLISHED => 'Terbit',
                Document::STATUS_ARCHIVED => 'Diarsipkan',
                default => 'Draf',
            },
        ];
    }

    /**
     * MIME yang diizinkan sesuai CLAUDE.md aturan 6:
     * PDF, DOCX, XLSX, PPTX, JPG, PNG — maks 50 MB.
     */
    public const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'image/jpeg',
        'image/png',
    ];

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dokumen')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, ?string $state, Set $set): void {
                                if ($operation === 'create') {
                                    $set('slug', Str::slug((string) $state));
                                }
                            }),
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->rules(['alpha_dash'])
                            ->helperText('Digunakan pada alamat URL dokumen.'),
                        ...self::categoryPickerComponents(),
                        Forms\Components\Select::make('visibility')
                            ->label('Visibilitas')
                            ->options([
                                Document::VISIBILITY_PUBLIC => 'Publik (tanpa login)',
                                Document::VISIBILITY_MAHASISWA => 'Mahasiswa',
                                Document::VISIBILITY_INTERNAL => 'Internal (dosen & admin)',
                            ])
                            ->default(Document::VISIBILITY_INTERNAL)
                            ->required()
                            ->helperText('Siapa yang boleh melihat dan mengunduh dokumen ini.'),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                Document::STATUS_DRAFT => 'Draf',
                                Document::STATUS_PUBLISHED => 'Terbit',
                                Document::STATUS_ARCHIVED => 'Diarsipkan',
                            ])
                            ->default(Document::STATUS_DRAFT)
                            ->required(),
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Dokumen unggulan')
                            ->helperText('Tampil menonjol di beranda.'),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Berkas')
                    ->schema([
                        Forms\Components\Radio::make('sumber')
                            ->label('Sumber dokumen')
                            ->options([
                                'file' => 'Unggah file ke server',
                                'tautan' => 'Tautan eksternal (Google Drive, OneDrive, dsb.) — hemat penyimpanan server',
                            ])
                            ->default('file')
                            ->live()
                            ->dehydrated(false)
                            ->afterStateHydrated(function (Forms\Components\Radio $component, ?Document $record): void {
                                $component->state($record?->isExternal() ? 'tautan' : 'file');
                            }),
                        Forms\Components\FileUpload::make('file_path')
                            ->label('Berkas dokumen')
                            ->disk('documents')
                            ->directory(fn (Get $get): string => self::storageDirectory($get('category_id'), $get('academic_year')))
                            ->getUploadedFileNameForStorageUsing(
                                fn (TemporaryUploadedFile $file, Get $get): string => Str::slug(
                                    $get('slug') ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)
                                ).'-'.now()->timestamp.'.'.strtolower($file->getClientOriginalExtension()),
                            )
                            ->acceptedFileTypes(self::ALLOWED_MIME_TYPES)
                            ->maxSize(51200) // 50 MB
                            ->visible(fn (Get $get): bool => $get('sumber') === 'file')
                            ->required(fn (string $operation, Get $get): bool => $operation === 'create' && $get('sumber') === 'file')
                            ->storeFileNamesIn('file_name')
                            ->helperText('Format: PDF, DOCX, XLSX, PPTX, JPG, PNG. Maksimal 50 MB.')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('external_url')
                            ->label('Tautan dokumen')
                            ->url()
                            ->maxLength(2048)
                            ->placeholder('https://drive.google.com/file/d/…/view')
                            ->visible(fn (Get $get): bool => $get('sumber') === 'tautan')
                            ->required(fn (Get $get): bool => $get('sumber') === 'tautan')
                            ->helperText('PENTING: atur berbagi tautan di Google Drive ke "Siapa saja yang memiliki link". Kontrol akses GESIT hanya membatasi siapa yang mendapat tautannya — file di Drive mengikuti pengaturan berbagi Drive itu sendiri.')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Metadata Akademik')
                    ->schema([
                        Forms\Components\TextInput::make('academic_year')
                            ->label('Tahun akademik')
                            ->placeholder('2025/2026')
                            ->regex('/^\d{4}\/\d{4}$/')
                            ->validationMessages(['regex' => 'Format tahun akademik harus seperti 2025/2026.']),
                        Forms\Components\Select::make('semester')
                            ->label('Semester')
                            ->options(['ganjil' => 'Ganjil', 'genap' => 'Genap', '-' => '-']),
                        Forms\Components\TextInput::make('course_name')
                            ->label('Nama mata kuliah')
                            ->maxLength(255)
                            ->helperText('Untuk RPS, modul, kontrak kuliah.'),
                        Forms\Components\TextInput::make('lecturer_name')
                            ->label('Nama dosen')
                            ->maxLength(255)
                            ->helperText('Untuk arsip dosen.'),
                        Forms\Components\DatePicker::make('expires_at')
                            ->label('Tanggal kedaluwarsa')
                            ->helperText('Untuk MoU/MoA — akan muncul peringatan 90 hari sebelum kedaluwarsa.'),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(45)
                    ->description(fn (Document $record): ?string => $record->course_name),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('visibility')
                    ->label('Visibilitas')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Document::VISIBILITY_PUBLIC => 'Publik',
                        Document::VISIBILITY_MAHASISWA => 'Mahasiswa',
                        Document::VISIBILITY_INTERNAL => 'Internal',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        Document::VISIBILITY_PUBLIC => 'success',
                        Document::VISIBILITY_MAHASISWA => 'warning',
                        Document::VISIBILITY_INTERNAL => 'danger',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Document::STATUS_DRAFT => 'Draf',
                        Document::STATUS_PUBLISHED => 'Terbit',
                        Document::STATUS_ARCHIVED => 'Diarsipkan',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        Document::STATUS_DRAFT => 'gray',
                        Document::STATUS_PUBLISHED => 'success',
                        Document::STATUS_ARCHIVED => 'warning',
                    }),
                Tables\Columns\TextColumn::make('academic_year')
                    ->label('Tahun Akademik')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('sumber')
                    ->label('Sumber')
                    ->state(fn (Document $record): string => $record->isExternal() ? 'Tautan' : 'File')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Tautan' ? 'info' : 'gray')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('file_size')
                    ->label('Ukuran')
                    ->formatStateUsing(fn (?int $state): string => $state ? Number::fileSize($state) : '—')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('download_count')
                    ->label('Unduhan')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Diunggah')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->defaultPaginationPageOption(25)
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->emptyStateHeading('Belum ada dokumen')
            ->emptyStateDescription('Unggah dokumen pertama lewat tombol di kanan atas, atau gunakan Unggah Massal untuk banyak berkas sekaligus.')
            ->filters([
                Tables\Filters\SelectFilter::make('kategori_utama')
                    ->label('Kategori utama')
                    ->options(fn (): array => Category::root()->pluck('name', 'id')->all())
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }

                        $ids = Category::query()
                            ->where('id', $data['value'])
                            ->orWhere('parent_id', $data['value'])
                            ->pluck('id');

                        return $query->whereIn('category_id', $ids);
                    }),
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('visibility')
                    ->label('Visibilitas')
                    ->options([
                        Document::VISIBILITY_PUBLIC => 'Publik',
                        Document::VISIBILITY_MAHASISWA => 'Mahasiswa',
                        Document::VISIBILITY_INTERNAL => 'Internal',
                    ]),
                Tables\Filters\SelectFilter::make('academic_year')
                    ->label('Tahun Akademik')
                    ->options(
                        fn (): array => Document::query()
                            ->whereNotNull('academic_year')
                            ->distinct()
                            ->orderByDesc('academic_year')
                            ->pluck('academic_year', 'academic_year')
                            ->all(),
                    ),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('lihat')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Document $record): string => route('documents.preview', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
        ];
    }

    /**
     * Pasangan select bertingkat: kategori utama dulu, lalu sub-kategori
     * menyesuaikan. Kategori utama tanpa sub memakai dirinya sendiri.
     * Dipakai form Dokumen dan halaman Unggah Massal.
     *
     * @return array<Forms\Components\Select>
     */
    public static function categoryPickerComponents(): array
    {
        $isLocked = fn ($livewire): bool => filled($livewire->lockedMainCategory ?? null);

        return [
            // Saat dibuka dari menu kategori sidebar: kategori utama terkunci,
            // ditampilkan sebagai keterangan saja
            Forms\Components\Placeholder::make('kategori_utama_terkunci')
                ->label('Kategori utama')
                ->content(fn ($livewire): string => Category::find($livewire->lockedMainCategory ?? null)?->name ?? '—')
                ->visible($isLocked),
            Forms\Components\Select::make('kategori_utama')
                ->label('Kategori utama')
                ->options(fn (): array => Category::root()->pluck('name', 'id')->all())
                ->required(fn ($livewire): bool => ! $isLocked($livewire))
                ->live()
                ->dehydrated(false)
                ->hidden($isLocked)
                ->afterStateHydrated(function (Forms\Components\Select $component, ?Document $record): void {
                    $category = $record?->category;

                    $component->state($category?->parent_id ?? $category?->id);
                })
                ->afterStateUpdated(function ($state, Set $set): void {
                    // Tanpa sub-kategori → dokumen langsung di kategori utama
                    $hasChildren = $state && Category::where('parent_id', $state)->exists();

                    $set('category_id', $hasChildren ? null : $state);
                }),
            Forms\Components\Select::make('category_id')
                ->label('Sub-kategori')
                ->options(fn (Get $get): array => self::subcategoryOptions($get('kategori_utama')))
                ->required()
                ->live()
                ->disabled(fn (Get $get): bool => blank($get('kategori_utama')))
                // Filament tidak memvalidasi opsi select secara otomatis —
                // pastikan sub-kategori memang milik kategori utama terpilih
                ->in(fn (Get $get): array => array_keys(self::subcategoryOptions($get('kategori_utama'))))
                ->helperText(fn ($livewire): ?string => $isLocked($livewire) ? null : 'Pilih kategori utama terlebih dahulu.'),
        ];
    }

    /**
     * Opsi sub-kategori dari satu kategori utama; kategori utama tanpa
     * sub memakai dirinya sendiri sebagai pilihan.
     *
     * @return array<int, string>
     */
    private static function subcategoryOptions(int|string|null $mainId): array
    {
        if (! $mainId) {
            return [];
        }

        $children = Category::where('parent_id', $mainId)
            ->orderBy('sort_order')
            ->pluck('name', 'id');

        return $children->isNotEmpty()
            ? $children->all()
            : Category::whereKey($mainId)->pluck('name', 'id')->all();
    }

    /**
     * Lokasi penyimpanan: {kategori-slug}/{tahun} di dalam disk `documents`
     * (= storage/app/documents/{kategori}/{tahun}/ sesuai CLAUDE.md).
     */
    public static function storageDirectory(int|string|null $categoryId, ?string $academicYear): string
    {
        $category = $categoryId ? Category::find($categoryId) : null;

        $year = preg_match('/^(\d{4})/', (string) $academicYear, $matches)
            ? $matches[1]
            : now()->format('Y');

        return ($category?->slug ?? 'tanpa-kategori').'/'.$year;
    }
}

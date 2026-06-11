<?php

namespace App\Filament\Pages;

use App\Filament\Resources\DocumentResource;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Document;
use App\Services\ActivityLogger;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class BulkUploadDocuments extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static ?string $navigationGroup = 'Manajemen Arsip';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Unggah Massal';

    protected static ?string $navigationLabel = 'Unggah Massal';

    protected static string $view = 'filament.pages.bulk-upload-documents';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'visibility' => Document::VISIBILITY_INTERNAL,
            'status' => Document::STATUS_DRAFT,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Forms\Components\Section::make('Metadata Bersama')
                    ->description('Diterapkan ke semua berkas yang diunggah. Judul tiap dokumen diambil dari nama berkasnya — rapikan lewat menu Dokumen bila perlu.')
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->label('Kategori')
                            ->options(fn (): array => Category::groupedSelectOptions())
                            ->searchable()
                            ->required()
                            ->live(),
                        Forms\Components\Select::make('visibility')
                            ->label('Visibilitas')
                            ->options([
                                Document::VISIBILITY_PUBLIC => 'Publik (tanpa login)',
                                Document::VISIBILITY_MAHASISWA => 'Mahasiswa',
                                Document::VISIBILITY_INTERNAL => 'Internal (dosen & admin)',
                            ])
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                Document::STATUS_DRAFT => 'Draf',
                                Document::STATUS_PUBLISHED => 'Terbit',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('academic_year')
                            ->label('Tahun akademik')
                            ->placeholder('2025/2026')
                            ->regex('/^\d{4}\/\d{4}$/')
                            ->validationMessages(['regex' => 'Format tahun akademik harus seperti 2025/2026.']),
                        Forms\Components\Select::make('semester')
                            ->label('Semester')
                            ->options(['ganjil' => 'Ganjil', 'genap' => 'Genap', '-' => '-']),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Berkas')
                    ->schema([
                        Forms\Components\FileUpload::make('files')
                            ->label('Berkas dokumen (bisa lebih dari satu)')
                            ->multiple()
                            ->minFiles(1)
                            ->maxFiles(30)
                            ->disk('documents')
                            ->directory(fn (Get $get): string => DocumentResource::storageDirectory($get('category_id'), $get('academic_year')))
                            ->getUploadedFileNameForStorageUsing(
                                fn (TemporaryUploadedFile $file): string => Str::slug(
                                    pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)
                                ).'-'.now()->timestamp.Str::lower(Str::random(4)).'.'.strtolower($file->getClientOriginalExtension()),
                            )
                            ->acceptedFileTypes(DocumentResource::ALLOWED_MIME_TYPES)
                            ->maxSize(51200) // 50 MB per file
                            ->storeFileNamesIn('file_names')
                            ->required()
                            ->helperText('Format: PDF, DOCX, XLSX, PPTX, JPG, PNG. Maksimal 50 MB per berkas, 30 berkas sekali unggah.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $disk = Storage::disk('documents');
        $originalNames = $data['file_names'] ?? [];
        $created = 0;

        foreach ($data['files'] as $path) {
            $originalName = $originalNames[$path] ?? basename($path);
            $title = Str::of(pathinfo($originalName, PATHINFO_FILENAME))
                ->replace(['-', '_'], ' ')
                ->squish()
                ->ucfirst()
                ->toString();

            $document = Document::create([
                'title' => $title,
                'slug' => $this->uniqueSlug($title),
                'category_id' => $data['category_id'],
                'file_path' => $path,
                'file_name' => $originalName,
                'file_size' => $disk->exists($path) ? $disk->size($path) : 0,
                'mime_type' => ($disk->exists($path) ? $disk->mimeType($path) : null) ?: 'application/octet-stream',
                'visibility' => $data['visibility'],
                'status' => $data['status'],
                'academic_year' => $data['academic_year'] ?? null,
                'semester' => $data['semester'] ?? null,
                'uploaded_by' => auth()->id(),
            ]);

            app(ActivityLogger::class)->log(ActivityLog::ACTION_UPLOAD, $document, request());

            $created++;
        }

        $this->form->fill([
            'category_id' => $data['category_id'],
            'visibility' => $data['visibility'],
            'status' => $data['status'],
            'academic_year' => $data['academic_year'] ?? null,
            'semester' => $data['semester'] ?? null,
        ]);

        Notification::make()
            ->success()
            ->title($created.' dokumen berhasil diunggah')
            ->body('Judul diambil dari nama berkas — periksa dan rapikan di menu Dokumen.')
            ->send();
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'dokumen';
        $slug = $base;
        $suffix = 1;

        while (Document::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}

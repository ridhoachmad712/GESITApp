<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Resources\DocumentResource;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Services\ActivityLogger;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateDocument extends CreateRecord
{
    protected static string $resource = DocumentResource::class;

    /**
     * Terisi saat halaman dibuka dari menu kategori di sidebar
     * (?kategori_utama=) — pemilihan kategori utama disembunyikan
     * dan sub-kategori dibatasi ke kategori tersebut.
     */
    public ?int $lockedMainCategory = null;

    public function mount(): void
    {
        parent::mount();

        $lockedId = request()->integer('kategori_utama');
        $locked = $lockedId
            ? Category::whereNull('parent_id')->find($lockedId)
            : null;

        if ($locked) {
            $this->lockedMainCategory = $locked->id;
            $this->data['kategori_utama'] = $locked->id;

            // Kategori utama tanpa sub → langsung terpilih
            if (Category::where('parent_id', $locked->id)->doesntExist()) {
                $this->data['category_id'] = $locked->id;
            }
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['uploaded_by'] = auth()->id();
        $data = self::normalizeSource($data);

        return $this->fillFileMetadata($data);
    }

    /**
     * Dokumen bersumber tunggal: tautan eksternal ATAU file unggahan.
     */
    public static function normalizeSource(array $data): array
    {
        if (filled($data['external_url'] ?? null)) {
            $data['file_path'] = null;
            $data['file_name'] = null;
            $data['file_size'] = null;
            $data['mime_type'] = null;
        } else {
            $data['external_url'] = null;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        app(ActivityLogger::class)->log(ActivityLog::ACTION_UPLOAD, $this->record, request());
    }

    private function fillFileMetadata(array $data): array
    {
        $disk = Storage::disk('documents');

        if (! empty($data['file_path']) && $disk->exists($data['file_path'])) {
            $data['file_size'] = $disk->size($data['file_path']);
            $data['mime_type'] = $disk->mimeType($data['file_path']) ?: 'application/octet-stream';
        }

        return $data;
    }
}

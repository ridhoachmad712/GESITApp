<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Resources\DocumentResource;
use App\Models\ActivityLog;
use App\Services\ActivityLogger;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditDocument extends EditRecord
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        // Tanpa ForceDeleteAction — CLAUDE.md aturan 3: tidak ada hard delete dari UI.
        return [
            Actions\DeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = CreateDocument::normalizeSource($data);

        $disk = Storage::disk('documents');

        // Perbarui metadata hanya jika berkas diganti
        if (! empty($data['file_path'])
            && $data['file_path'] !== $this->record->file_path
            && $disk->exists($data['file_path'])) {
            $data['file_size'] = $disk->size($data['file_path']);
            $data['mime_type'] = $disk->mimeType($data['file_path']) ?: 'application/octet-stream';
        }

        return $data;
    }

    protected function afterSave(): void
    {
        app(ActivityLogger::class)->log(ActivityLog::ACTION_UPDATE, $this->record, request());
    }
}

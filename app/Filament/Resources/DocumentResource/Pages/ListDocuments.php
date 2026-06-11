<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Resources\DocumentResource;
use App\Models\Document;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListDocuments extends ListRecords
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Saat daftar sedang terfilter kategori utama (dari menu sidebar
            // "Kategori Arsip"), form Buat langsung terkunci ke kategori itu
            Actions\CreateAction::make()
                ->url(function (): string {
                    $mainCategoryId = $this->getTableFilterState('kategori_utama')['value'] ?? null;

                    return DocumentResource::getUrl('create', array_filter([
                        'kategori_utama' => $mainCategoryId,
                    ]));
                }),
        ];
    }

    /**
     * Tab status di atas tabel — lebih cepat daripada membuka filter.
     */
    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua')
                ->badge(Document::count()),
            'terbit' => Tab::make('Terbit')
                ->badge(Document::where('status', Document::STATUS_PUBLISHED)->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', Document::STATUS_PUBLISHED)),
            'draf' => Tab::make('Draf')
                ->badge(Document::where('status', Document::STATUS_DRAFT)->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', Document::STATUS_DRAFT)),
            'diarsipkan' => Tab::make('Diarsipkan')
                ->badge(Document::where('status', Document::STATUS_ARCHIVED)->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', Document::STATUS_ARCHIVED)),
        ];
    }
}

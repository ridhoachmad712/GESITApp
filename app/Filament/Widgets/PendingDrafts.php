<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\DocumentResource;
use App\Models\Document;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PendingDrafts extends BaseWidget
{
    protected static ?int $sort = 2;

    protected static bool $isLazy = false;

    protected static ?string $heading = 'Draf Menunggu Diterbitkan';

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Document::query()
                    ->with(['category', 'uploader'])
                    ->where('status', Document::STATUS_DRAFT)
                    ->oldest()
                    ->limit(8),
            )
            ->paginated(false)
            ->recordUrl(fn (Document $record): string => DocumentResource::getUrl('edit', ['record' => $record]))
            ->emptyStateHeading('Tidak ada draf menunggu')
            ->emptyStateDescription('Semua unggahan sudah diterbitkan.')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->limit(40),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori'),
                Tables\Columns\TextColumn::make('uploader.name')
                    ->label('Pengunggah')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Diunggah')
                    ->since(),
            ]);
    }
}

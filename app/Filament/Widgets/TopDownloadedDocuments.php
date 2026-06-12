<?php

namespace App\Filament\Widgets;

use App\Models\Document;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopDownloadedDocuments extends BaseWidget
{
    protected static ?int $sort = 4;

    protected static bool $isLazy = false;

    protected static ?string $heading = 'Dokumen Terpopuler';

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Document::query()
                    ->with('category')
                    ->where('download_count', '>', 0)
                    ->orderByDesc('download_count')
                    ->limit(10),
            )
            ->paginated(false)
            ->emptyStateHeading('Belum ada unduhan')
            ->emptyStateIcon('heroicon-o-arrow-down-tray')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->limit(50),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori'),
                Tables\Columns\TextColumn::make('visibility')
                    ->label('Visibilitas')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        Document::VISIBILITY_PUBLIC => 'success',
                        Document::VISIBILITY_MAHASISWA => 'warning',
                        Document::VISIBILITY_INTERNAL => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('download_count')
                    ->label('Unduhan')
                    ->numeric()
                    ->alignRight(),
                Tables\Columns\TextColumn::make('view_count')
                    ->label('Dilihat')
                    ->numeric()
                    ->alignRight(),
            ]);
    }
}

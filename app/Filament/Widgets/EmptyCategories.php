<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class EmptyCategories extends BaseWidget
{
    protected static ?int $sort = 7;

    protected static bool $isLazy = false;

    protected static ?string $heading = 'Kategori Tanpa Dokumen (Gap Analysis)';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Category::query()
                    ->with('parent')
                    ->whereDoesntHave('documents')
                    // Kategori induk dianggap terisi jika sub-kategorinya punya dokumen
                    ->whereDoesntHave('children.documents')
                    ->orderBy('parent_id')
                    ->orderBy('sort_order'),
            )
            ->paginated([10, 25, 50])
            ->emptyStateHeading('Semua kategori sudah terisi dokumen 🎉')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Kategori'),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Kategori utama')
                    ->placeholder('— kategori utama'),
            ]);
    }
}

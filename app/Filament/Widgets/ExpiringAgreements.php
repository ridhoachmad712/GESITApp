<?php

namespace App\Filament\Widgets;

use App\Models\Document;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ExpiringAgreements extends BaseWidget
{
    protected static ?int $sort = 6;

    protected static ?string $heading = 'MoU/MoA Akan Kedaluwarsa (< 90 hari)';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Document::query()
                    ->with('category')
                    ->whereNotNull('expires_at')
                    ->whereBetween('expires_at', [today(), today()->addDays(90)])
                    ->orderBy('expires_at'),
            )
            ->paginated(false)
            ->emptyStateHeading('Tidak ada dokumen yang akan kedaluwarsa')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->limit(50),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori'),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Kedaluwarsa')
                    ->date('d M Y')
                    ->color('danger')
                    ->description(fn (Document $record): string => today()->diffInDays($record->expires_at, false).' hari lagi'),
            ]);
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use App\Models\ActivityLog;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'Log Aktivitas';

    protected static ?string $pluralModelLabel = 'Log Aktivitas';

    /**
     * Log bersifat jejak audit: hanya bisa dibaca,
     * tidak bisa dibuat/diubah/dihapus dari UI.
     */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('action')
                    ->label('Aksi')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        ActivityLog::ACTION_VIEW => 'Lihat',
                        ActivityLog::ACTION_DOWNLOAD => 'Unduh',
                        ActivityLog::ACTION_UPLOAD => 'Unggah',
                        ActivityLog::ACTION_UPDATE => 'Ubah',
                        ActivityLog::ACTION_DELETE => 'Hapus',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        ActivityLog::ACTION_VIEW => 'gray',
                        ActivityLog::ACTION_DOWNLOAD => 'info',
                        ActivityLog::ACTION_UPLOAD => 'success',
                        ActivityLog::ACTION_UPDATE => 'warning',
                        ActivityLog::ACTION_DELETE => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pengguna')
                    ->placeholder('Pengunjung publik')
                    ->searchable(),
                Tables\Columns\TextColumn::make('document.title')
                    ->label('Dokumen')
                    ->limit(45)
                    ->placeholder('—')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('Alamat IP')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user_agent')
                    ->label('Peramban')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->label('Aksi')
                    ->options([
                        ActivityLog::ACTION_VIEW => 'Lihat',
                        ActivityLog::ACTION_DOWNLOAD => 'Unduh',
                        ActivityLog::ACTION_UPLOAD => 'Unggah',
                        ActivityLog::ACTION_UPDATE => 'Ubah',
                        ActivityLog::ACTION_DELETE => 'Hapus',
                    ]),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Pengguna')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('periode')
                    ->form([
                        Forms\Components\DatePicker::make('dari')->label('Dari tanggal'),
                        Forms\Components\DatePicker::make('sampai')->label('Sampai tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['dari'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['sampai'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
        ];
    }
}

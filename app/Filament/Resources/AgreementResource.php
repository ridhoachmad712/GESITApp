<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AgreementResource\Pages;
use App\Models\Agreement;
use App\Models\Document;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AgreementResource extends Resource
{
    protected static ?string $model = Agreement::class;

    protected static ?string $navigationIcon = 'heroicon-o-hand-raised';

    protected static ?string $navigationGroup = 'Konten Situs';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Kerja Sama';

    protected static ?string $pluralModelLabel = 'Kerja Sama';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Judul kerja sama')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('partner_name')
                    ->label('Mitra')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->label('Jenis')
                    ->options(array_combine(Agreement::TYPES, Agreement::TYPES))
                    ->required(),
                Forms\Components\DatePicker::make('start_date')
                    ->label('Tanggal mulai'),
                Forms\Components\DatePicker::make('end_date')
                    ->label('Tanggal berakhir')
                    ->afterOrEqual('start_date')
                    ->helperText('Kosongkan jika tidak ada batas waktu.'),
                Forms\Components\Select::make('document_id')
                    ->label('File perjanjian (dokumen internal)')
                    ->options(
                        fn (): array => Document::query()
                            ->orderBy('title')
                            ->pluck('title', 'id')
                            ->all(),
                    )
                    ->searchable()
                    ->placeholder('— tidak ditautkan —')
                    ->helperText('Tautkan ke dokumen ber-visibility internal. Tidak pernah tampil ke publik.'),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi / ruang lingkup')
                    ->rows(3)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('partner_name')
                    ->label('Mitra')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Berakhir')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('Tanpa batas'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->state(fn (Agreement $record): string => $record->isActive() ? 'Aktif' : 'Kedaluwarsa')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Aktif' ? 'success' : 'danger'),
            ])
            ->defaultSort('end_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis')
                    ->options(array_combine(Agreement::TYPES, Agreement::TYPES)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAgreements::route('/'),
            'create' => Pages\CreateAgreement::route('/create'),
            'edit' => Pages\EditAgreement::route('/{record}/edit'),
        ];
    }
}

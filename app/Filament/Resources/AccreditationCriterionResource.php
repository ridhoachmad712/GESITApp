<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccreditationCriterionResource\Pages;
use App\Models\AccreditationCriterion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AccreditationCriterionResource extends Resource
{
    protected static ?string $model = AccreditationCriterion::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';

    protected static ?string $navigationGroup = 'Manajemen Arsip';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'Kriteria Akreditasi';

    protected static ?string $pluralModelLabel = 'Kriteria Akreditasi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label('Kode')
                    ->required()
                    ->maxLength(10)
                    ->placeholder('K1'),
                Forms\Components\TextInput::make('name')
                    ->label('Nama kriteria')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('instrument')
                    ->label('Instrumen')
                    ->required()
                    ->default('LAMEMBA')
                    ->maxLength(50),
                Forms\Components\TextInput::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->default(0),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('instrument')
                    ->label('Instrumen')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('documents_count')
                    ->label('Jumlah Bukti')
                    ->counts('documents')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Tables\Actions\DeleteAction $action, AccreditationCriterion $record): void {
                        if ($record->documents()->exists()) {
                            Notification::make()
                                ->danger()
                                ->title('Kriteria tidak dapat dihapus')
                                ->body('Masih ada dokumen yang ditautkan ke kriteria ini.')
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccreditationCriteria::route('/'),
            'create' => Pages\CreateAccreditationCriterion::route('/create'),
            'edit' => Pages\EditAccreditationCriterion::route('/{record}/edit'),
        ];
    }
}

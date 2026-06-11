<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LecturerResource\Pages;
use App\Models\Lecturer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LecturerResource extends Resource
{
    protected static ?string $model = Lecturer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationGroup = 'Konten Situs';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Dosen';

    protected static ?string $pluralModelLabel = 'Dosen';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama lengkap (dengan gelar)')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('nidn')
                    ->label('NIDN')
                    ->maxLength(30)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('position')
                    ->label('Jabatan fungsional')
                    ->placeholder('mis. Lektor Kepala')
                    ->maxLength(255),
                Forms\Components\TextInput::make('expertise')
                    ->label('Bidang keahlian')
                    ->placeholder('mis. Manajemen Keuangan')
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255),
                Forms\Components\TextInput::make('publication_url')
                    ->label('Tautan publikasi (SINTA/Scholar)')
                    ->url()
                    ->maxLength(255),
                Forms\Components\FileUpload::make('photo_path')
                    ->label('Foto')
                    ->image()
                    ->disk('public')
                    ->directory('dosen')
                    ->maxSize(2048)
                    ->imageEditor()
                    ->helperText('JPG/PNG maks 2 MB. Foto tampil di halaman publik.'),
                Forms\Components\TextInput::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_active')
                    ->label('Tampilkan di halaman publik')
                    ->default(true),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo_path')
                    ->label('Foto')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl('https://ui-avatars.com/api/?background=F77F00&color=fff&name=D'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nidn')
                    ->label('NIDN')
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('expertise')
                    ->label('Bidang keahlian')
                    ->placeholder('—'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Tampil')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status tampil'),
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
            'index' => Pages\ListLecturers::route('/'),
            'create' => Pages\CreateLecturer::route('/create'),
            'edit' => Pages\EditLecturer::route('/{record}/edit'),
        ];
    }
}

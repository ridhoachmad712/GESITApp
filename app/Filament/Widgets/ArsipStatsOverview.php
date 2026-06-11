<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Document;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ArsipStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Dokumen', Document::count())
                ->description(Document::published()->count().' terbit')
                ->icon('heroicon-o-document-text'),
            Stat::make('Total Unduhan', Document::sum('download_count'))
                ->description('Sepanjang waktu')
                ->icon('heroicon-o-arrow-down-tray'),
            Stat::make('Kategori', Category::whereNull('parent_id')->count())
                ->description(Category::whereNotNull('parent_id')->count().' sub-kategori')
                ->icon('heroicon-o-folder'),
            Stat::make('Menunggu Aktivasi', User::where('is_active', false)->count())
                ->description('Pendaftar baru')
                ->color(User::where('is_active', false)->exists() ? 'warning' : 'success')
                ->icon('heroicon-o-user-plus'),
        ];
    }
}

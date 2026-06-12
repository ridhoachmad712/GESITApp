<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ActivityLogResource;
use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\DocumentResource;
use App\Filament\Resources\UserResource;
use App\Models\ActivityLog;
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
        $pendingUsers = User::where('is_active', false)->count();

        return [
            Stat::make('Total Dokumen', Document::count())
                ->description(Document::published()->count().' terbit — klik untuk kelola')
                ->icon('heroicon-o-document-text')
                ->url(DocumentResource::getUrl('index')),
            Stat::make('Total Unduhan', Document::sum('download_count'))
                ->description('Klik untuk log unduhan')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(ActivityLogResource::getUrl('index', [
                    'tableFilters' => ['action' => ['value' => ActivityLog::ACTION_DOWNLOAD]],
                ])),
            Stat::make('Kategori', Category::whereNull('parent_id')->count())
                ->description(Category::whereNotNull('parent_id')->count().' sub-kategori')
                ->icon('heroicon-o-folder')
                ->url(CategoryResource::getUrl('index')),
            Stat::make('Menunggu Aktivasi', $pendingUsers)
                ->description($pendingUsers > 0 ? 'Klik untuk mengaktifkan' : 'Tidak ada pendaftar baru')
                ->color($pendingUsers > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-user-plus')
                ->url(UserResource::getUrl('index', [
                    'tableFilters' => ['is_active' => ['value' => 0]],
                ])),
        ];
    }
}

<?php

namespace App\Filament\Widgets;

use App\Models\ActivityLog;
use App\Models\Setting;
use Filament\Widgets\ChartWidget;

class ActivityTrendChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected static bool $isLazy = false;

    protected static ?string $heading = 'Tren Aktivitas 6 Bulan Terakhir';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $start = now()->subMonths(5)->startOfMonth();

        $counts = ActivityLog::query()
            ->whereIn('action', [ActivityLog::ACTION_DOWNLOAD, ActivityLog::ACTION_VIEW])
            ->where('created_at', '>=', $start)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as bulan, action, COUNT(*) as aggregate")
            ->groupBy('bulan', 'action')
            ->get()
            ->groupBy('action');

        $labels = [];
        $downloads = [];
        $views = [];

        foreach (range(0, 5) as $offset) {
            $month = (clone $start)->addMonths($offset);
            $key = $month->format('Y-m');

            $labels[] = $month->translatedFormat('M Y');
            $downloads[] = (int) ($counts->get(ActivityLog::ACTION_DOWNLOAD)?->firstWhere('bulan', $key)?->aggregate ?? 0);
            $views[] = (int) ($counts->get(ActivityLog::ACTION_VIEW)?->firstWhere('bulan', $key)?->aggregate ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Unduhan',
                    'data' => $downloads,
                    'backgroundColor' => Setting::get('primary_color') ?? '#1E3A8A',
                ],
                [
                    'label' => 'Dilihat',
                    'data' => $views,
                    'backgroundColor' => '#9CA3AF',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}

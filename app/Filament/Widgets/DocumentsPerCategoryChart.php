<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Document;
use Filament\Widgets\ChartWidget;

class DocumentsPerCategoryChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected static ?string $heading = 'Dokumen per Kategori';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $countsPerCategory = Document::query()
            ->selectRaw('category_id, COUNT(*) as aggregate')
            ->groupBy('category_id')
            ->pluck('aggregate', 'category_id');

        $roots = Category::with('children')->root()->get();

        $labels = [];
        $values = [];

        foreach ($roots as $root) {
            $labels[] = $root->name;
            $values[] = ($countsPerCategory[$root->id] ?? 0)
                + $root->children->sum(fn (Category $child): int => $countsPerCategory[$child->id] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah dokumen',
                    'data' => $values,
                    'backgroundColor' => '#1E3A8A',
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

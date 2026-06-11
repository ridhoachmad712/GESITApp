<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Document;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        // Jumlah dokumen publik-terbit per kategori utama (termasuk sub-kategorinya)
        $countsPerCategory = Document::published()->public()
            ->selectRaw('category_id, COUNT(*) as aggregate')
            ->groupBy('category_id')
            ->pluck('aggregate', 'category_id');

        $categories = Category::with('children')->root()->get()
            ->map(function (Category $category) use ($countsPerCategory): Category {
                $category->public_documents_count = ($countsPerCategory[$category->id] ?? 0)
                    + $category->children->sum(
                        fn (Category $child): int => $countsPerCategory[$child->id] ?? 0,
                    );

                return $category;
            });

        return view('home', [
            'categories' => $categories,
            'totalPublicDocuments' => $countsPerCategory->sum(),
            'totalCategories' => $categories->count(),
            'featuredDocuments' => Document::published()->public()
                ->where('is_featured', true)
                ->with('category')
                ->latest()
                ->take(3)
                ->get(),
            'latestDocuments' => Document::published()->public()
                ->with('category')
                ->latest()
                ->take(6)
                ->get(),
        ]);
    }
}

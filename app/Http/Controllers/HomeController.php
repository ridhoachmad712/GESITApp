<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Document;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        // Statistik beranda dihitung dari sudut pandang publik (tanpa login).
        // Kategori yang masih kosong diturunkan ke bawah agar pengunjung
        // langsung melihat kategori yang berisi.
        $categories = Category::rootsWithVisibleDocumentCounts(null)
            ->sortBy(fn (Category $category): int => $category->visible_documents_count > 0 ? 0 : 1)
            ->values();

        return view('home', [
            'categories' => $categories,
            'totalPublicDocuments' => $categories->sum('visible_documents_count'),
            'totalCategories' => $categories->count(),
            'featuredDocuments' => Document::published()->public()
                ->where('is_featured', true)
                ->with('category')
                ->latest()
                ->take(3)
                ->get(),
            'popularDocuments' => Document::published()->public()
                ->where('download_count', '>', 0)
                ->with('category')
                ->orderByDesc('download_count')
                ->take(6)
                ->get(),
            'latestDocuments' => Document::published()->public()
                ->with('category')
                ->latest()
                ->take(6)
                ->get(),
        ]);
    }
}

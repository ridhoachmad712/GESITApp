<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Document;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        // Statistik beranda dihitung dari sudut pandang publik (tanpa login)
        $categories = Category::rootsWithVisibleDocumentCounts(null);

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
            'latestDocuments' => Document::published()->public()
                ->with('category')
                ->latest()
                ->take(6)
                ->get(),
        ]);
    }
}

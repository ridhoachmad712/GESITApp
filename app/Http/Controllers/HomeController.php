<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Document;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public const CACHE_KEY = 'home-page-data';

    public function index(): View
    {
        // Semua data beranda dihitung dari sudut pandang publik (tanpa login),
        // jadi aman di-cache global. Di-bust saat dokumen/kategori berubah,
        // plus TTL 5 menit sebagai pengaman.
        $data = Cache::remember(self::CACHE_KEY, 300, function (): array {
            // Kategori kosong diturunkan ke bawah agar yang berisi terlihat dulu
            $categories = Category::rootsWithVisibleDocumentCounts(null)
                ->sortBy(fn (Category $category): int => $category->visible_documents_count > 0 ? 0 : 1)
                ->values();

            return [
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
            ];
        });

        return view('home', $data);
    }
}

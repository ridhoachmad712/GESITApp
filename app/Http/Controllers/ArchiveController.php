<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArchiveController extends Controller
{
    /**
     * Daftar semua kategori arsip.
     */
    public function index(Request $request): View
    {
        return view('arsip.index', [
            'categories' => Category::rootsWithVisibleDocumentCounts($request->user()),
        ]);
    }

    /**
     * Kategori utama dengan sub-kategori → hub pemilihan sub dulu
     * (agar pengunjung tidak ditumpahi semua dokumen sekaligus).
     * Sub-kategori, kategori tanpa sub, atau ?semua=1 → daftar dokumen.
     */
    public function show(Request $request, Category $category): View
    {
        $category->load(['children', 'parent']);

        if ($category->children->isNotEmpty() && ! $request->boolean('semua')) {
            return $this->hub($request, $category);
        }

        $categoryIds = [$category->id, ...$category->children->pluck('id')->all()];

        $base = Document::published()
            ->visibleTo($request->user())
            ->whereIn('category_id', $categoryIds);

        $years = (clone $base)
            ->whereNotNull('academic_year')
            ->distinct()
            ->orderByDesc('academic_year')
            ->pluck('academic_year');

        $documents = (clone $base)
            ->when(
                $request->filled('tahun'),
                fn ($query) => $query->where('academic_year', $request->string('tahun')),
            )
            ->with('category')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('arsip.show', [
            'category' => $category,
            'documents' => $documents,
            'years' => $years,
            'selectedYear' => (string) $request->string('tahun'),
            'viewMode' => $request->query('tampilan') === 'list' ? 'list' : 'grid',
            'showAll' => $request->boolean('semua'),
        ]);
    }

    /**
     * Hub sub-kategori: jumlah dokumen & pembaruan terakhir per sub
     * (sesuai hak akses pengunjung), pencarian terbatas kategori,
     * dan jalan pintas melihat semua dokumen.
     */
    private function hub(Request $request, Category $category): View
    {
        $stats = Document::published()
            ->visibleTo($request->user())
            ->whereIn('category_id', $category->children->pluck('id')->push($category->id))
            ->selectRaw('category_id, COUNT(*) as aggregate, MAX(created_at) as latest')
            ->groupBy('category_id')
            ->get()
            ->keyBy('category_id');

        return view('arsip.hub', [
            'category' => $category,
            'stats' => $stats,
            'totalDocuments' => (int) $stats->sum('aggregate'),
        ]);
    }
}

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
     * Dokumen dalam satu kategori (termasuk sub-kategorinya),
     * dengan filter tahun akademik dan pagination.
     */
    public function show(Request $request, Category $category): View
    {
        $categoryIds = [$category->id, ...$category->children()->pluck('id')->all()];

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
            'category' => $category->load(['children', 'parent']),
            'documents' => $documents,
            'years' => $years,
            'selectedYear' => (string) $request->string('tahun'),
            'viewMode' => $request->query('tampilan') === 'list' ? 'list' : 'grid',
        ]);
    }
}

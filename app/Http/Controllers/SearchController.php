<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    /**
     * Pencarian FULLTEXT pada judul + deskripsi.
     * Hasil mengikuti visibility pengunjung; opsional dibatasi
     * satu kategori (termasuk sub-kategorinya) via ?kategori={slug}.
     */
    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $documents = null;

        $categoryFilter = $request->filled('kategori')
            ? Category::with('children')->where('slug', $request->string('kategori'))->first()
            : null;

        if ($query !== '') {
            $base = Document::published()
                ->visibleTo($request->user())
                ->when($categoryFilter, function ($builder) use ($categoryFilter): void {
                    $builder->whereIn('category_id', [
                        $categoryFilter->id,
                        ...$categoryFilter->children->pluck('id')->all(),
                    ]);
                })
                ->with('category');

            // Kata < 3 huruf tidak terindeks FULLTEXT InnoDB — pakai LIKE
            $documents = (mb_strlen($query) < 3
                    ? $base->where(function ($where) use ($query): void {
                        $where->where('title', 'like', "%{$query}%")
                            ->orWhere('description', 'like', "%{$query}%");
                    })
                    : $base->whereFullText(['title', 'description'], $query))
                ->latest()
                ->paginate(12)
                ->withQueryString();
        }

        return view('cari', [
            'query' => $query,
            'documents' => $documents,
            'categoryFilter' => $categoryFilter,
        ]);
    }
}

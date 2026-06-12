<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    /**
     * Pencarian FULLTEXT pada judul + deskripsi.
     * Hasil mengikuti visibility pengunjung; bisa dibatasi kategori
     * (?kategori={slug}, termasuk sub-kategorinya) dan tahun (?tahun=).
     */
    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $documents = null;

        $categoryFilter = $request->filled('kategori')
            ? Category::with('children')->where('slug', $request->string('kategori'))->first()
            : null;

        $base = Document::published()
            ->visibleTo($request->user())
            ->when($categoryFilter, function ($builder) use ($categoryFilter): void {
                $builder->whereIn('category_id', [
                    $categoryFilter->id,
                    ...$categoryFilter->children->pluck('id')->all(),
                ]);
            });

        $years = (clone $base)
            ->whereNotNull('academic_year')
            ->distinct()
            ->orderByDesc('academic_year')
            ->pluck('academic_year');

        if ($query !== '') {
            $scoped = (clone $base)
                ->when(
                    $request->filled('tahun'),
                    fn ($builder) => $builder->where('academic_year', $request->string('tahun')),
                )
                ->with('category');

            // Kata < 3 huruf tidak terindeks FULLTEXT InnoDB — pakai LIKE
            $documents = (mb_strlen($query) < 3
                    ? $scoped->where(function ($where) use ($query): void {
                        $where->where('title', 'like', "%{$query}%")
                            ->orWhere('description', 'like', "%{$query}%");
                    })
                    : $scoped->whereFullText(['title', 'description'], $query))
                ->latest()
                ->paginate(12)
                ->withQueryString();
        }

        return view('cari', [
            'query' => $query,
            'documents' => $documents,
            'categoryFilter' => $categoryFilter,
            'categoryOptions' => $this->categoryOptions(),
            'years' => $years,
            'selectedYear' => (string) $request->string('tahun'),
        ]);
    }

    /**
     * Saran pencarian (autocomplete) — JSON maks 5 judul,
     * sesuai visibility pengunjung.
     */
    public function suggest(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json([]);
        }

        $categoryFilter = $request->filled('kategori')
            ? Category::with('children')->where('slug', $request->string('kategori'))->first()
            : null;

        $documents = Document::published()
            ->visibleTo($request->user())
            ->when($categoryFilter, function ($builder) use ($categoryFilter): void {
                $builder->whereIn('category_id', [
                    $categoryFilter->id,
                    ...$categoryFilter->children->pluck('id')->all(),
                ]);
            })
            ->where('title', 'like', "%{$query}%")
            ->with('category')
            ->latest()
            ->limit(5)
            ->get();

        return response()->json(
            $documents->map(fn (Document $document): array => [
                'title' => $document->title,
                'category' => $document->category->name,
                'url' => route('documents.show', $document),
            ])->all(),
        );
    }

    /**
     * Opsi filter kategori (slug => nama, sub diberi indentasi).
     *
     * @return array<string, string>
     */
    private function categoryOptions(): array
    {
        $options = [];

        foreach (Category::with('children')->root()->get() as $root) {
            $options[$root->slug] = $root->name;

            foreach ($root->children as $child) {
                $options[$child->slug] = '— '.$child->name;
            }
        }

        return $options;
    }
}

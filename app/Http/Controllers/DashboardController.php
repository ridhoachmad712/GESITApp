<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dashboard user login: dokumen sesuai role
     * (mahasiswa: public+mahasiswa; dosen/admin: semua),
     * dengan filter kategori, tahun, semester, dan pencarian.
     */
    public function index(Request $request): View
    {
        $base = Document::published()->visibleTo($request->user());

        $years = (clone $base)
            ->whereNotNull('academic_year')
            ->distinct()
            ->orderByDesc('academic_year')
            ->pluck('academic_year');

        $search = trim((string) $request->query('q', ''));

        $documents = (clone $base)
            ->when(
                $request->filled('kategori'),
                fn ($query) => $query->where('category_id', $request->integer('kategori')),
            )
            ->when(
                $request->filled('tahun'),
                fn ($query) => $query->where('academic_year', $request->string('tahun')),
            )
            ->when(
                $request->filled('semester'),
                fn ($query) => $query->where('semester', $request->string('semester')),
            )
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($where) use ($search): void {
                    $where->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('course_name', 'like', "%{$search}%")
                        ->orWhere('lecturer_name', 'like', "%{$search}%");
                });
            })
            ->with('category')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('dashboard', [
            'documents' => $documents,
            'categoryOptions' => Category::groupedSelectOptions(),
            'years' => $years,
            'filters' => [
                'kategori' => (string) $request->string('kategori'),
                'tahun' => (string) $request->string('tahun'),
                'semester' => (string) $request->string('semester'),
                'q' => $search,
            ],
        ]);
    }
}

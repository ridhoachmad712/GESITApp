<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DokumentasiController extends Controller
{
    /**
     * Galeri foto kegiatan: seluruh dokumen gambar public+published
     * (kategori apa pun — kategori tidak di-hardcode), dengan
     * filter tahun akademik.
     */
    public function index(Request $request): View
    {
        $base = Document::published()
            ->public()
            ->where('mime_type', 'like', 'image/%')
            ->whereNotNull('file_path');

        $years = (clone $base)
            ->whereNotNull('academic_year')
            ->distinct()
            ->orderByDesc('academic_year')
            ->pluck('academic_year');

        $photos = (clone $base)
            ->when(
                $request->filled('tahun'),
                fn ($query) => $query->where('academic_year', $request->string('tahun')),
            )
            ->with('category')
            ->latest()
            ->paginate(24)
            ->withQueryString();

        return view('dokumentasi.index', [
            'photos' => $photos,
            'years' => $years,
            'selectedYear' => (string) $request->string('tahun'),
        ]);
    }
}

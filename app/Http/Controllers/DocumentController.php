<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DocumentController extends Controller
{
    /**
     * Halaman detail dokumen: metadata, preview, tombol unduh.
     * Counter view dan log dicatat oleh endpoint preview/unduh.
     */
    public function show(Request $request, Document $document): View|RedirectResponse
    {
        if (! Gate::forUser($request->user())->allows('view', $document)) {
            if ($request->user() === null) {
                return redirect()->guest(route('login'));
            }

            abort(403, 'Anda tidak memiliki izin untuk mengakses dokumen ini.');
        }

        $relatedDocuments = Document::published()
            ->visibleTo($request->user())
            ->where('category_id', $document->category_id)
            ->whereKeyNot($document->id)
            ->latest()
            ->take(3)
            ->get();

        return view('documents.show', [
            'document' => $document->load(['category.parent', 'uploader']),
            'relatedDocuments' => $relatedDocuments,
        ]);
    }
}

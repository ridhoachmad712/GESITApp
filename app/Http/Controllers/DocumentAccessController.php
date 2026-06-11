<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Document;
use App\Services\ActivityLogger;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class DocumentAccessController extends Controller
{
    public function __construct(private readonly ActivityLogger $activityLogger) {}

    /**
     * Unduh file dokumen dari storage privat setelah cek policy.
     */
    public function download(Request $request, Document $document): Response
    {
        $this->authorizeAccess($request, 'download', $document);

        // Dokumen di penyimpanan eksternal (mis. Google Drive) — alihkan
        // setelah cek policy; unduhan tetap tercatat
        if ($document->isExternal()) {
            $document->increment('download_count');
            $this->activityLogger->log(ActivityLog::ACTION_DOWNLOAD, $document, $request);

            return redirect()->away($document->external_url);
        }

        abort_unless($document->file_path && Storage::disk('documents')->exists($document->file_path), 404);

        $document->increment('download_count');
        $this->activityLogger->log(ActivityLog::ACTION_DOWNLOAD, $document, $request);

        return Storage::disk('documents')->download($document->file_path, $document->file_name);
    }

    /**
     * Sajikan file inline (untuk preview PDF.js) setelah cek policy.
     */
    public function preview(Request $request, Document $document): Response
    {
        $this->authorizeAccess($request, 'view', $document);

        if ($document->isExternal()) {
            $document->increment('view_count');
            $this->activityLogger->log(ActivityLog::ACTION_VIEW, $document, $request);

            return redirect()->away($document->external_url);
        }

        abort_unless($document->file_path && Storage::disk('documents')->exists($document->file_path), 404);

        $document->increment('view_count');
        $this->activityLogger->log(ActivityLog::ACTION_VIEW, $document, $request);

        return Storage::disk('documents')->response($document->file_path, $document->file_name);
    }

    /**
     * Pengunjung tanpa login diarahkan ke halaman login;
     * user login tanpa hak mendapat 403.
     */
    private function authorizeAccess(Request $request, string $ability, Document $document): void
    {
        if (Gate::forUser($request->user())->allows($ability, $document)) {
            return;
        }

        if ($request->user() === null) {
            throw new HttpResponseException(redirect()->guest(route('login')));
        }

        abort(403, 'Anda tidak memiliki izin untuk mengakses dokumen ini.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\AccreditationCriterion;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BundelAkreditasiController extends Controller
{
    /**
     * Bundel Akreditasi (F3.3): seluruh bukti terkumpul per kriteria
     * LAMEMBA — untuk persiapan & simulasi asesmen. Khusus dosen/admin
     * (dibatasi middleware role di route).
     */
    public function index(Request $request): View
    {
        $criteria = AccreditationCriterion::ordered()
            ->with(['documents' => function ($query) use ($request): void {
                $query->published()
                    ->visibleTo($request->user())
                    ->with('category')
                    ->latest();
            }])
            ->get();

        return view('bundel', [
            'criteria' => $criteria,
            'totalEvidence' => $criteria->sum(fn (AccreditationCriterion $criterion): int => $criterion->documents->count()),
        ]);
    }
}

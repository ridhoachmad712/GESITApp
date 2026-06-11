<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use Illuminate\View\View;

class KerjasamaController extends Controller
{
    /**
     * Daftar MoU/MoA/IA untuk publik — metadata saja, TANPA tautan file.
     */
    public function index(): View
    {
        return view('kerjasama.index', [
            'agreements' => Agreement::query()
                ->orderByRaw('end_date IS NULL DESC')
                ->orderByDesc('end_date')
                ->paginate(15),
        ]);
    }
}

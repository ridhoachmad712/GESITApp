<?php

namespace App\Http\Controllers;

use App\Models\Lecturer;
use App\Models\Page;
use Illuminate\View\View;

class ProfilController extends Controller
{
    public function index(): View
    {
        return view('profil.index', [
            'pages' => Page::orderBy('id')->get(['title', 'slug']),
            'lecturerCount' => Lecturer::active()->count(),
        ]);
    }

    public function show(Page $page): View
    {
        return view('profil.show', ['page' => $page]);
    }

    public function dosen(): View
    {
        return view('profil.dosen', [
            'lecturers' => Lecturer::active()->get(),
        ]);
    }
}

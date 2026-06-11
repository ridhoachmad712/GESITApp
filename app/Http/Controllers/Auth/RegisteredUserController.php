<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'identity_number' => ['required', 'string', 'max:30', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Registrasi mandiri hanya untuk mahasiswa dan harus disetujui admin
        // (PLAN F1.8) — akun dibuat nonaktif, tidak langsung login.
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'identity_number' => $request->identity_number,
            'password' => Hash::make($request->password),
            'role' => User::ROLE_MAHASISWA,
            'is_active' => false,
        ]);

        event(new Registered($user));

        return redirect(route('login'))
            ->with('status', 'Pendaftaran berhasil. Akun Anda akan aktif setelah diverifikasi oleh admin prodi.');
    }
}

<?php

namespace App\Filament\Pages\Auth;

use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    /**
     * Panel Filament khusus admin, tapi dosen/mahasiswa yang keliru
     * login lewat /admin tidak ditolak — dialihkan ke dashboard user.
     */
    public function authenticate(): ?LoginResponse
    {
        try {
            return parent::authenticate();
        } catch (ValidationException $exception) {
            $data = $this->form->getState();

            if (Auth::attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {
                $user = Auth::user();

                if ($user->is_active && ! $user->isAdmin()) {
                    session()->regenerate();
                    $this->redirect(route('dashboard'));

                    return null;
                }

                // Kredensial benar tapi akun nonaktif — tetap tolak
                Auth::logout();
            }

            throw $exception;
        }
    }
}

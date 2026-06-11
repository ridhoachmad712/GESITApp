<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    /**
     * Hierarki visibility: public < mahasiswa < internal.
     * Admin/dosen ≥ internal, mahasiswa ≥ mahasiswa, publik (tanpa login) hanya public.
     * User nonaktif diperlakukan sebagai pengunjung publik.
     */
    public function view(?User $user, Document $document): bool
    {
        if ($user !== null && ! $user->is_active) {
            $user = null;
        }

        // Admin boleh melihat semua dokumen, termasuk draft/arsip
        if ($user?->isAdmin()) {
            return true;
        }

        if ($document->status !== Document::STATUS_PUBLISHED) {
            return false;
        }

        return match ($document->visibility) {
            Document::VISIBILITY_PUBLIC => true,
            Document::VISIBILITY_MAHASISWA => $user !== null,
            Document::VISIBILITY_INTERNAL => $user !== null && $user->isDosen(),
            default => false,
        };
    }

    public function download(?User $user, Document $document): bool
    {
        return $this->view($user, $document);
    }

    // ---- Ability CRUD untuk panel admin (Filament) ----

    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Document $document): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Document $document): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Document $document): bool
    {
        return $user->isAdmin();
    }

    /**
     * CLAUDE.md aturan 3: tidak ada hard delete dari UI.
     */
    public function forceDelete(User $user, Document $document): bool
    {
        return false;
    }
}

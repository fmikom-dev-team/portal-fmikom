<?php

namespace App\Services\Wims\Mahasiswa\Profile;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StudentProfileUpdateService
{
    public function update(User $user, array $validated, ?UploadedFile $photo): void
    {
        // Field profil dibersihkan lebih dulu supaya data kosong tersimpan konsisten sebagai null,
        // bukan campuran string kosong dan null.
        $updates = [
            'no_telepon' => $validated['no_telepon'] ?? null,
            'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
            'bio' => $this->nullIfBlank($validated['bio'] ?? null),
            'website' => $this->normalizeUrl($validated['website'] ?? null),
            'linkedin' => $this->normalizeUrl($validated['linkedin'] ?? null),
        ];

        if (($validated['remove_photo'] ?? false) && $user->foto_path) {
            Storage::disk('public')->delete($user->foto_path);
            $updates['foto_path'] = null;
        }

        if ($photo) {
            if ($user->foto_path) {
                Storage::disk('public')->delete($user->foto_path);
            }

            // File lama diganti agar storage tidak menumpuk foto profil yang sudah tidak dipakai.
            $updates['foto_path'] = $photo->store('profile-photos', 'public');
        }

        $user->forceFill($updates)->save();
    }

    private function nullIfBlank(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function normalizeUrl(?string $value): ?string
    {
        $trimmed = $this->nullIfBlank($value);

        return $trimmed !== null ? $trimmed : null;
    }
}

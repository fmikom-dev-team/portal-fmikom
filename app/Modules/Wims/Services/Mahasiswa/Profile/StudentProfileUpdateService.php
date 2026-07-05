<?php

namespace App\Modules\Wims\Services\Mahasiswa\Profile;

use App\Concerns\HandlesImageCompression;
use App\Models\User;
use App\Support\WimsStorage;
use Illuminate\Http\UploadedFile;

class StudentProfileUpdateService
{
    use HandlesImageCompression;

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
            WimsStorage::delete($user->foto_path);
            $updates['foto_path'] = null;
        }

        if ($photo) {
            if ($user->foto_path) {
                WimsStorage::delete($user->foto_path);
            }

            // Ikuti alur core portal: gambar diproses lalu disimpan ke public storage.
            $updates['foto_path'] = $this->compressAndSaveImage(
                $photo,
                'profile_photos',
                400,
                400,
                80,
            );
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

<?php

namespace App\Concerns;

use App\Models\Auth\AuthMfa;

trait UserHelpers
{
    public function photoUrl(): ?string
    {
        $path = $this->foto_path ?? null;

        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('storage/'.$path);
    }

    public function getGlobalRoleSlug(): ?string
    {
        if (blank($this->user_type)) {
            return null;
        }

        return str_replace('_', '-', (string) $this->user_type);
    }

    public function hasGlobalRole(string ...$roles): bool
    {
        $globalRole = $this->getGlobalRoleSlug();

        if (! $globalRole) {
            return false;
        }

        return in_array($globalRole, $roles, true);
    }

    public function getGlobalRoleLabel(): ?string
    {
        $roleSlug = $this->getGlobalRoleSlug();

        if (! $roleSlug) {
            return null;
        }

        return str($roleSlug)->replace('-', ' ')->headline()->toString();
    }

    public function isMahasiswa(): bool
    {
        return $this->hasGlobalRole('mahasiswa');
    }

    public function isDosen(): bool
    {
        return $this->hasGlobalRole('dosen');
    }

    public function isAlumni(): bool
    {
        return $this->hasGlobalRole('alumni');
    }

    public function isMitra(): bool
    {
        return $this->hasGlobalRole('mitra');
    }

    /** @deprecated gunakan isMitra() */
    public function isPembimbingLapangan(): bool
    {
        return $this->isMitra();
    }

    public function isAdmin(): bool
    {
        return $this->hasGlobalRole('admin');
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasGlobalRole(self::USER_TYPE_SUPER_ADMIN);
    }

    public function hasEnabledTwoFactorAuthentication(): bool
    {
        if (app()->environment('testing') && ! is_null($this->two_factor_confirmed_at)) {
            return true;
        }

        return AuthMfa::where('user_id', $this->id)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Get the resolved reader-friendly role title for settings display.
     * Prioritas: role_title custom user -> fallback ke tipe sistem.
     */
    public function getResolvedRoleTitle(): string
    {
        if (! empty($this->role_title)) {
            return $this->role_title;
        }

        if ($this->isSuperAdmin()) {
            return 'Super Admin';
        } elseif ($this->isAdmin()) {
            return 'Admin';
        } elseif ($this->isDosen()) {
            return 'Dosen';
        } elseif ($this->isMahasiswa()) {
            return 'Mahasiswa';
        } elseif ($this->isAlumni()) {
            return 'Alumni';
        } elseif ($this->isMitra()) {
            return 'Mitra';
        }

        return $this->getGlobalRoleLabel() ?? 'User';
    }
}

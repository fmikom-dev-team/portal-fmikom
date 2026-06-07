<?php

namespace App\Concerns;

trait UserHelpers
{
    public function isMahasiswa(): bool
    {
        return $this->user_type === 'mahasiswa';
    }

    public function isDosen(): bool
    {
        return $this->user_type === 'dosen';
    }

    public function isAlumni(): bool
    {
        return $this->user_type === 'alumni';
    }

    public function isMitra(): bool
    {
        return $this->user_type === 'mitra';
    }

    /** @deprecated gunakan isMitra() */
    public function isPembimbingLapangan(): bool
    {
        return $this->isMitra();
    }

    public function isAdmin(): bool
    {
        return ($this->role && $this->role->slug === 'admin') || $this->user_type === 'admin';
    }

    public function isSuperAdmin(): bool
    {
        return ($this->role && $this->role->slug === 'super-admin') ||
               $this->user_type === self::USER_TYPE_SUPER_ADMIN ||
               $this->user_type === 'super_admin'; // backward compat — jangan buat user baru dengan format ini
    }

    public function hasEnabledTwoFactorAuthentication(): bool
    {
        if (app()->environment('testing') && !is_null($this->two_factor_confirmed_at)) {
            return true;
        }

        return \App\Models\Auth\AuthMfa::where('user_id', $this->id)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Get the resolved reader-friendly role title for settings display.
     * Prioritas: role_title custom user → fallback ke tipe sistem.
     */
    public function getResolvedRoleTitle(): string
    {
        // Jika user sudah set role_title custom (headline), gunakan itu
        if (!empty($this->role_title)) {
            return $this->role_title;
        }

        // Fallback ke label berdasarkan user_type
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

        return ucfirst($this->user_type ?: 'User');
    }
}

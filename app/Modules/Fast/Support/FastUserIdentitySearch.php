<?php

namespace App\Modules\Fast\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class FastUserIdentitySearch
{
    protected static ?bool $hasLegacyNimNipColumn = null;

    public static function apply(Builder $query, string $search): void
    {
        $query->where('name', 'like', "%{$search}%");

        if (self::hasLegacyNimNipColumn()) {
            $query->orWhere('nim_nip', 'like', "%{$search}%");
        }

        $query->orWhere('nomor_induk', 'like', "%{$search}%");
    }

    /**
     * @param  array<int, string>  $columns
     * @return array<int, string>
     */
    public static function selectColumns(array $columns): array
    {
        if (! self::hasLegacyNimNipColumn()) {
            return $columns;
        }

        if (! in_array('nim_nip', $columns, true)) {
            $columns[] = 'nim_nip';
        }

        return $columns;
    }

    public static function resolveIdentifier(User $user): ?string
    {
        if (filled($user->nomor_induk)) {
            return $user->nomor_induk;
        }

        if (self::hasLegacyNimNipColumn()) {
            return $user->nim_nip;
        }

        return null;
    }

    public static function hasLegacyNimNipColumn(): bool
    {
        if (self::$hasLegacyNimNipColumn !== null) {
            return self::$hasLegacyNimNipColumn;
        }

        return self::$hasLegacyNimNipColumn = Schema::hasColumn('users', 'nim_nip');
    }
}

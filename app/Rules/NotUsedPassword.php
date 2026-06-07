<?php

namespace App\Rules;

use App\Models\Auth\AuthSetting;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Translation\PotentiallyTranslatedString;

class NotUsedPassword implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Resolve user: either currently authenticated user, or looking up by email (e.g., during password resets)
        $user = Auth::user();
        if (! $user && request()->has('email')) {
            $user = User::where('email', request()->email)->first();
        }

        if (! $user) {
            return;
        }

        // Get setting for history check count
        $limit = (int) AuthSetting::get('password.history_count', 5);
        if ($limit <= 0) {
            return;
        }

        // Fetch recent password hashes
        $hashes = DB::table('auth_password_histories')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->pluck('password_hash');

        foreach ($hashes as $hash) {
            if (Hash::check($value, $hash)) {
                $fail('Anda tidak dapat menggunakan kata sandi yang sudah pernah digunakan baru-baru ini.');

                return;
            }
        }
    }
}

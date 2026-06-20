<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'role_title' => 'Mahasiswa',
            'user_type' => 'mahasiswa',
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'tanggal_lahir' => null,
            'tahun_lulus' => null,
            'otp_code' => null,
            'otp_expires_at' => null,
            'password_changed_at' => null,
            'banner_path' => null,
            'bio' => null,
            'location' => null,
            'metadata' => null,
            'website' => null,
            'twitter' => null,
            'linkedin' => null,
            'github' => null,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (User $user): void {
            $userType = trim((string) ($user->user_type ?? ''));
            $roleTitle = trim((string) ($user->role_title ?? ''));

            $user->user_type = $userType !== '' ? $userType : 'mahasiswa';
            $user->role_title = $roleTitle !== '' ? $roleTitle : 'Mahasiswa';
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }
}

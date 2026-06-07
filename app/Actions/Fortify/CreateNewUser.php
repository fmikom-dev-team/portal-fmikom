<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Mail\SendOtpEmail;
use App\Models\Module;
use App\Models\Role;
use App\Models\User;
use App\Models\UserModuleRole;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        // Role yang diizinkan untuk self-register
        $allowedRoles = ['mahasiswa', 'alumni', 'mitra'];

        $rules = [
            ...$this->profileRules(),
            'role' => ['required', 'string', Rule::in($allowedRoles)],
            'nomor_induk' => ['required', 'string', 'max:50', Rule::unique('users', 'nomor_induk')],
            'password' => $this->passwordRules(),
        ];

        if (($input['role'] ?? '') === 'mahasiswa' || ($input['role'] ?? '') === 'alumni') {
            $rules['program_studi_id'] = ['required', 'integer', Rule::exists('program_studis', 'id')];
        }

        if (($input['role'] ?? '') === 'alumni') {
            $rules['tahun_lulus'] = ['required', 'digits:4', 'integer', 'min:1990', 'max:'.date('Y')];
        }

        if (($input['role'] ?? '') === 'mitra') {
            $rules['no_telepon'] = ['required', 'string', 'max:20'];
        }

        Validator::make($input, $rules, [
            'email.unique' => 'Akun dengan Email ini telah terdaftar, silakan login.',
            'nomor_induk.unique' => 'Akun dengan NIM/NIB ini telah terdaftar, silakan login.',
            'program_studi_id.required' => 'Program Studi wajib dipilih.',
            'tahun_lulus.required' => 'Tahun lulus wajib diisi.',
            'no_telepon.required' => 'Nomor telepon wajib diisi.',
        ])->validate();

        // Buat user — email belum terverifikasi, status langsung approved
        // (tidak ada waiting room, user langsung bisa akses setelah verifikasi OTP)
        // user_type = identity layer yang menentukan "siapa" user ini (menggantikan role_id lama)
        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'user_type' => $input['role'], // 'mahasiswa' | 'alumni' | 'mitra'
            'nomor_induk' => $input['nomor_induk'],
            'status_approval' => 'approved',
            'password_changed_at' => now(), // Sudah set password sendiri saat daftar
            // Field opsional berdasarkan role
            'program_studi_id' => $input['program_studi_id'] ?? null,
            'tahun_lulus' => $input['tahun_lulus'] ?? null,
            'no_telepon' => $input['no_telepon'] ?? null,
        ]);

        // Auto-assign default module access berdasarkan role/user_type
        $roleObj = Role::where('slug', $user->user_type)->first();
        if ($roleObj) {
            $defaultModules = [];
            if ($user->user_type === 'mahasiswa') {
                $defaultModules = ['FAST', 'PAGI', 'WIMS'];
            } elseif ($user->user_type === 'alumni') {
                $defaultModules = ['TRACE', 'PAGI'];
            } elseif ($user->user_type === 'mitra') {
                $defaultModules = ['WIMS', 'TRACE'];
            }

            if (! empty($defaultModules)) {
                $modules = Module::whereIn('code', $defaultModules)->get();
                foreach ($modules as $mod) {
                    UserModuleRole::create([
                        'user_id' => $user->id,
                        'module_id' => $mod->id,
                        'role_id' => $roleObj->id,
                        'is_active' => true,
                    ]);
                }
            }
        }

        // Kirim OTP ke email setelah akun dibuat
        $this->generateAndSendOtp($user);

        return $user;
    }

    /**
     * Generate 6-digit OTP, hash, simpan, dan kirim ke email user.
     */
    private function generateAndSendOtp(User $user): void
    {
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->forceFill([
            'otp_code' => Hash::make($otp),
            'otp_expires_at' => now()->addMinutes(15),
        ])->save();

        try {
            Mail::to($user->email)->queue(new SendOtpEmail($user, $otp));
        } catch (\Exception $e) {
            Log::error('Gagal mengirim OTP ke '.$user->email.': '.$e->getMessage());
        }

        // Hanya log di local untuk debugging — JANGAN di production
        if (app()->isLocal()) {
            Log::info("[DEV ONLY] OTP untuk {$user->email}: {$otp}");
        }
    }
}

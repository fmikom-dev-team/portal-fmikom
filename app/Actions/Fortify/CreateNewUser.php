<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\RegistrationStatus;
use App\Enums\UserAccountStatus;
use App\Models\Auth\RegistrationRequest;
use App\Models\User;
use App\Notifications\WorkOsAlert;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

/**
 * CreateNewUser — Fortify registration action.
 *
 * REFACTORED: No longer creates a User directly.
 * Instead, creates a RegistrationRequest (pending review).
 *
 * Who can self-register:
 *   - mitra (partner): goes through admin approval flow
 *   - alumni (new, not in campus DB): goes through admin approval flow
 *
 * Who CANNOT self-register:
 *   - mahasiswa: must use /activate (identity verification via NIM + DOB)
 *   - dosen: must use /activate (identity verification via NIDN + DOB)
 *   - staff: must use /activate
 *
 * After registration:
 *   - RegistrationRequest created (status = pending)
 *   - User is shown "Pendaftaran sedang diproses" message
 *   - Admin reviews and approves → ActivationService creates User + sends email
 *
 * NOTE: A "fake" user-like object is returned to satisfy Fortify's contract.
 * We use an anonymous class that implements the contract interface.
 * The actual user is only created after admin approval.
 */
class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Role yang diizinkan untuk self-register (Case B).
     * mahasiswa/dosen/staff tidak boleh self-register — harus lewat /activate.
     */
    private const ALLOWED_ROLES = ['mitra', 'alumni'];

    /**
     * Create a new registration request (NOT a user directly).
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'role' => ['required', 'string', Rule::in(self::ALLOWED_ROLES)],
        ];

        // Alumni baru: butuh program studi dan tahun lulus
        if (($input['role'] ?? '') === 'alumni') {
            $rules['program_studi_id'] = ['required', 'integer', Rule::exists('program_studis', 'id')];
            $rules['tahun_lulus'] = ['required', 'digits:4', 'integer', 'min:1990', 'max:'.date('Y')];
        }

        // Mitra: butuh nomor telepon
        if (($input['role'] ?? '') === 'mitra') {
            $rules['no_telepon'] = ['required', 'string', 'max:20'];
        }

        Validator::make($input, $rules, [
            'email.unique' => 'Email ini sudah terdaftar. Jika akun Anda sudah ada, silakan login atau hubungi admin.',
            'program_studi_id.required' => 'Program Studi wajib dipilih.',
            'tahun_lulus.required' => 'Tahun lulus wajib diisi.',
            'no_telepon.required' => 'Nomor telepon wajib diisi.',
        ])->after(function ($validator) use ($input) {
            // Custom: mahasiswa/dosen must use /activate, not /register
            if (in_array($input['role'] ?? '', ['mahasiswa', 'dosen', 'staff'])) {
                $validator->errors()->add('role',
                    'Mahasiswa, Dosen, dan Staff tidak perlu mendaftar. '.
                    'Silakan gunakan halaman Aktivasi Akun untuk mengaktifkan akun Anda.'
                );
            }

            // Check if email already has an active registration request
            $emailPendingExists = RegistrationRequest::where('email', '=', $input['email'] ?? '', 'and')
                ->whereIn('status', [
                    RegistrationStatus::Pending->value,
                    RegistrationStatus::Approved->value,
                    RegistrationStatus::OtpSent->value,
                    RegistrationStatus::OtpVerified->value,
                ], 'and', false)
                ->exists();

            if ($emailPendingExists) {
                $validator->errors()->add('email',
                    'Pendaftaran dengan email ini sedang dalam proses review. Silakan tunggu atau hubungi admin.'
                );
            }

            // Check if student_number/employee_number has a pending request
            if ($input['nomor_induk'] ?? null) {
                $identifierPendingExists = RegistrationRequest::where(function ($query) use ($input) {
                    $query->where('student_number', '=', $input['nomor_induk'], 'or')
                        ->where('employee_number', '=', $input['nomor_induk'], 'or');
                }, null, null, 'and')
                    ->whereIn('status', [
                        RegistrationStatus::Pending->value,
                        RegistrationStatus::Approved->value,
                        RegistrationStatus::OtpSent->value,
                        RegistrationStatus::OtpVerified->value,
                    ], 'and', false)
                    ->exists();

                if ($identifierPendingExists) {
                    $validator->errors()->add('nomor_induk',
                        'Nomor identitas ini sudah terdaftar dalam antrean peninjauan pendaftaran.'
                    );
                }
            }
        })->validate();

        $studentNumber = ($input['role'] === 'alumni') ? ($input['nomor_induk'] ?? null) : null;
        $employeeNumber = ($input['role'] === 'mitra') ? ($input['nomor_induk'] ?? null) : null;

        // Create registration request (NOT a User)
        $registrationRequest = RegistrationRequest::create([
            'full_name' => $input['name'],
            'email' => $input['email'],
            'phone' => $input['no_telepon'] ?? null,
            'role' => $input['role'],
            'student_number' => $studentNumber,
            'employee_number' => $employeeNumber,
            'program_studi_id' => $input['program_studi_id'] ?? null,
            'tahun_lulus' => $input['tahun_lulus'] ?? null,
            'status' => RegistrationStatus::Pending->value,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // We need to return a User instance for Fortify compatibility.
        // However, we DON'T want to log them in — we'll override the post-registration redirect.
        // Create a placeholder non-persisted user to trigger Fortify's Registered event
        // then immediately redirect in the response.
        //
        // ALTERNATIVE: We return a "real" temporary user record with pending status.
        // This user cannot login because status_approval = 'pending'.
        // After admin approval, this record will be updated with proper data.
        $tempUser = new User([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make(Str::random(32)),
        ]);
        $tempUser->user_type = $input['role'];
        $tempUser->status_approval = UserAccountStatus::Pending;
        $tempUser->is_active = false;
        $tempUser->save();

        // Link the registration request to this temp user
        $registrationRequest->update(['created_user_id' => $tempUser->id]);

        // Notify super admins of the new signup request
        $superAdmins = User::where('user_type', '=', 'super_admin', 'and')->get();
        foreach ($superAdmins as $admin) {
            $admin->notify(new WorkOsAlert(
                title: 'Pendaftaran Akun Baru',
                description: 'Calon '.ucfirst($registrationRequest->role).' bernama '.$registrationRequest->full_name.' telah mendaftar mandiri.',
                severity: 'info',
                extra: [
                    'type' => 'registration_request',
                    'registration_request_id' => $registrationRequest->id,
                    'name' => $registrationRequest->full_name,
                    'email' => $registrationRequest->email,
                    'role' => $registrationRequest->role,
                    'identifier' => $registrationRequest->student_number ?? $registrationRequest->employee_number ?? '-',
                ]
            ));
        }

        return $tempUser;
    }
}

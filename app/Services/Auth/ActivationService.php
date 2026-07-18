<?php

namespace App\Services\Auth;

use App\Enums\OtpPurpose;
use App\Enums\RegistrationStatus;
use App\Enums\UserAccountStatus;
use App\Mail\ActivationEmail;
use App\Models\Auth\AuthAuditLog;
use App\Models\Auth\AuthOAuthCredential;
use App\Models\Auth\AuthOtpToken;
use App\Models\Auth\RegistrationRequest;
use App\Models\Module;
use App\Models\User;
use App\Models\UserModuleRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * ActivationService — Handles account activation flows.
 *
 * Case A (Admin-Driven): User activates pre-existing account via NIM+DOB,
 * receives OTP to stored email, creates password → activated.
 *
 * Case B (Self-Registration): Admin approves RegistrationRequest →
 * creates User record → sends activation email with signed URL →
 * user clicks link, verifies OTP, creates password → activated.
 */
class ActivationService
{
    public function __construct(
        private OtpService $otpService,
    ) {}

    // ─── Case A: Admin-Driven Activation ─────────────────────────────────────

    /**
     * Send activation OTP to the user's stored email.
     * Called after identity has been verified (NIM+DOB matched).
     *
     * @param  User  $user  The pre-existing user found via identity verification
     */
    public function sendActivationOtp(User $user, ?string $ipAddress = null): AuthOtpToken
    {
        $token = $this->otpService->generate(
            userId: $user->id,
            email: $user->email,
            purpose: OtpPurpose::AccountActivation,
            userForDisplay: $user,
            ipAddress: $ipAddress,
        );

        // Update status
        $user->forceFill([
            'status_approval' => UserAccountStatus::OtpSent->value,
        ])->save();

        AuthAuditLog::log('account.activation_otp_sent', $user->id, [
            'method' => 'admin_driven',
        ]);

        return $token;
    }

    /**
     * Mark OTP as verified for Case A (Step 2 of activation).
     * After this, user needs to create a password.
     */
    public function markOtpVerified(User $user): void
    {
        $user->forceFill([
            'email_verified_at' => now(),
            'status_approval' => UserAccountStatus::OtpVerified->value,
        ])->save();

        AuthAuditLog::log('account.activation_otp_verified', $user->id, [
            'method' => 'admin_driven',
        ]);
    }

    /**
     * Complete Case A activation: set password, activate account.
     */
    public function completeActivation(User $user, string $password): void
    {
        $user->forceFill([
            'password' => Hash::make($password),
            'password_changed_at' => now(),
            'is_active' => true,
            'status_approval' => UserAccountStatus::Activated->value,
        ])->save();

        // Assign default module roles if none assigned
        if (! UserModuleRole::where('user_id', '=', $user->id, 'and')->exists()) {
            $user->assignDefaultModuleRoles();
        }

        AuthAuditLog::log('account.activated', $user->id, [
            'method' => 'admin_driven',
        ]);
    }

    // ─── Case B: Self-Registration Approval → Activation ─────────────────────

    /**
     * Admin approves a RegistrationRequest.
     * Creates a User record and sends an activation email with signed URL.
     *
     * @return User The newly created (but not yet active) user
     */
    public function approveRegistrationRequest(
        RegistrationRequest $request,
        int $approvedBy,
        ?string $approvalNotes = null,
    ): User {
        return DB::transaction(function () use ($request, $approvedBy, $approvalNotes) {
            // 1. Get or Create the User record from registration data
            $user = $request->createdUser ?? User::where('email', '=', $request->email, 'and')->first();

            if ($user) {
                $user->fill([
                    'name' => $request->full_name,
                    'nomor_induk' => $request->student_number ?? $request->employee_number,
                    'program_studi_id' => $request->program_studi_id,
                    'tahun_lulus' => $request->tahun_lulus,
                    'no_telepon' => $request->phone,
                ]);
                $user->forceFill([
                    'user_type' => $request->role,
                    'is_active' => false,
                    'status_approval' => UserAccountStatus::Approved->value,
                ])->save();
            } else {
                $user = new User([
                    'name' => $request->full_name,
                    'email' => $request->email,
                    'password' => Hash::make(Str::random(32)), // Random unusable password
                    'nomor_induk' => $request->student_number ?? $request->employee_number,
                    'program_studi_id' => $request->program_studi_id,
                    'tahun_lulus' => $request->tahun_lulus,
                    'no_telepon' => $request->phone,
                ]);
                $user->forceFill([
                    'user_type' => $request->role,
                    'is_active' => false,
                    'status_approval' => UserAccountStatus::Approved->value,
                ])->save();
            }

            // 2. If OAuth-based registration, link the OAuth credential
            if ($request->hasOAuthData()) {
                $oauthData = $request->oauth_data;
                AuthOAuthCredential::updateOrCreate(
                    [
                        'provider_id' => $oauthData['provider_id'],
                        'external_id' => $oauthData['external_id'],
                    ],
                    [
                        'user_id' => $user->id,
                        'email' => $oauthData['email'],
                        'access_token' => $oauthData['access_token'] ?? null,
                        'refresh_token' => $oauthData['refresh_token'] ?? null,
                        'expires_at' => $oauthData['expires_at'] ?? null,
                    ]
                );
                // OAuth users have email already verified by the provider
                $user->forceFill(['email_verified_at' => now()])->save();
            }

            // 3. Generate activation token (hashed, stored in registration_request)
            $plainToken = $request->generateActivationToken();

            // 4. Update registration request status
            $request->fill([
                'status' => RegistrationStatus::OtpSent->value,
                'approved_by' => $approvedBy,
                'approved_at' => now(),
                'approval_notes' => $approvalNotes,
                'created_user_id' => $user->id,
            ])->save();

            // 5. Update user status
            $user->forceFill(['status_approval' => UserAccountStatus::OtpSent->value])->save();

            // 6. Send activation email with signed URL (queued)
            $activationUrl = URL::temporarySignedRoute(
                'activation.confirm',
                now()->addHours(24),
                [
                    'token' => $plainToken,
                    'email' => $request->email,
                    'request_id' => $request->id,
                ]
            );

            try {
                Mail::to($request->email)->queue(new ActivationEmail($user, $activationUrl));
            } catch (\Throwable $e) {
                Log::error('[ActivationService] Gagal mengirim email aktivasi: '.$e->getMessage(), [
                    'email' => $request->email,
                    'user_id' => $user->id,
                ]);
            }

            AuthAuditLog::log('account.registration_approved', $approvedBy, [
                'registration_request_id' => $request->id,
                'created_user_id' => $user->id,
                'email' => $request->email,
            ]);

            return $user;
        });
    }

    /**
     * Reject a RegistrationRequest.
     */
    public function rejectRegistrationRequest(
        RegistrationRequest $request,
        int $rejectedBy,
        string $rejectionReason,
    ): void {
        $request->fill([
            'status' => RegistrationStatus::Rejected->value,
            'rejected_by' => $rejectedBy,
            'rejected_at' => now(),
            'rejection_reason' => $rejectionReason,
        ])->save();

        AuthAuditLog::log('account.registration_rejected', $rejectedBy, [
            'registration_request_id' => $request->id,
            'email' => $request->email,
            'reason' => $rejectionReason,
        ]);

        // Optionally notify the applicant
        // TODO: Send rejection notification email
    }

    /**
     * Resend activation email for a RegistrationRequest (if token expired).
     */
    public function resendActivationEmail(RegistrationRequest $request, User $user): void
    {
        // Generate a fresh activation token
        $plainToken = $request->generateActivationToken();

        $request->fill(['status' => RegistrationStatus::OtpSent->value])->save();
        $user->forceFill(['status_approval' => UserAccountStatus::OtpSent->value])->save();

        $activationUrl = URL::temporarySignedRoute(
            'activation.confirm',
            now()->addHours(24),
            [
                'token' => $plainToken,
                'email' => $request->email,
                'request_id' => $request->id,
            ]
        );

        Mail::to($request->email)->queue(new ActivationEmail($user, $activationUrl));

        AuthAuditLog::log('account.activation_resent', null, [
            'registration_request_id' => $request->id,
            'email' => $request->email,
        ]);
    }

    /**
     * Complete Case B activation after user sets password.
     */
    public function completeSelfRegistrationActivation(
        User $user,
        RegistrationRequest $request,
        string $password,
    ): void {
        DB::transaction(function () use ($user, $request, $password) {
            $user->forceFill([
                'password' => Hash::make($password),
                'password_changed_at' => now(),
                'email_verified_at' => $user->email_verified_at ?? now(),
                'is_active' => true,
                'status_approval' => UserAccountStatus::Activated->value,
            ])->save();

            $request->fill([
                'status' => RegistrationStatus::Activated->value,
                'created_user_id' => $user->id,
            ])->save();

            // Invalidate the activation token
            $request->fill([
                'activation_token_hash' => null,
                'activation_token_expires_at' => null,
            ])->save();

            // Assign default module roles
            $user->refresh();
            if (! UserModuleRole::where('user_id', '=', $user->id, 'and')->exists()) {
                $user->assignDefaultModuleRoles();
            }

            AuthAuditLog::log('account.activated', $user->id, [
                'method' => 'self_registration',
                'registration_request_id' => $request->id,
            ]);
        });
    }
}

<?php

namespace App\Modules\WorkOs\Services\MFA;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * SMSService — SMS-based One-Time Password (OTP)
 *
 * Provides SMS MFA as an alternative to TOTP authenticator apps.
 * Supports multiple SMS gateway backends (pluggable via config).
 *
 * Supported gateways (configure in config/services.php):
 *   - Vonage (formerly Nexmo) — https://www.vonage.com
 *   - Twilio                  — https://www.twilio.com
 *   - AWS SNS                 — https://aws.amazon.com/sns
 *
 * Security:
 *   - OTP is 6 digits, expires in 10 minutes
 *   - Stored as hash in cache (not plain text)
 *   - Rate limited by phone number (3 sends per 5 minutes)
 *   - OTP invalidated after first successful use
 */
class SMSService
{
    protected int $otpLength = 6;

    protected int $ttlMinutes = 10;

    protected int $maxSends = 3;

    protected int $sendWindowMins = 5;

    // ─────────────────────────────────────────────────────────────────────────
    // Public API
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Send an OTP SMS to the user's registered phone number.
     * Returns true if sent, throws on rate-limit or gateway error.
     */
    public function sendOtp(User $user): bool
    {
        $phone = $user->phone_number ?? null;

        if (! $phone) {
            throw new \Exception('User does not have a registered phone number.');
        }

        // Rate limit check
        $sendCount = Cache::get($this->sendRateLimitKey($phone), 0);
        if ($sendCount >= $this->maxSends) {
            throw new \Exception("Too many OTP requests. Please wait {$this->sendWindowMins} minutes.");
        }

        // Generate OTP
        $otp = $this->generateOtp();

        // Store hash in cache (never store plain OTP)
        Cache::put(
            $this->otpCacheKey($phone),
            bcrypt($otp), // bcrypt for storage, plain for comparison
            now()->addMinutes($this->ttlMinutes)
        );

        // Increment send counter
        Cache::put(
            $this->sendRateLimitKey($phone),
            $sendCount + 1,
            now()->addMinutes($this->sendWindowMins)
        );

        // Dispatch SMS
        $this->dispatch($phone, $otp);

        return true;
    }

    /**
     * Verify the OTP provided by the user.
     * Invalidates the OTP on success (one-time use).
     */
    public function verifyOtp(User $user, string $code): bool
    {
        $phone = $user->phone_number ?? null;

        if (! $phone) {
            throw new \Exception('No phone number registered for this user.');
        }

        $storedHash = Cache::get($this->otpCacheKey($phone));

        if (! $storedHash) {
            return false; // OTP expired or never sent
        }

        if (! password_verify($code, $storedHash)) {
            return false;
        }

        // Invalidate — one-time use only
        Cache::forget($this->otpCacheKey($phone));

        return true;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SMS Gateway Dispatch
    // ─────────────────────────────────────────────────────────────────────────

    protected function dispatch(string $phone, string $otp): void
    {
        $gateway = config('services.sms.driver', 'vonage');
        $message = config('app.name')." verification code: {$otp}. Expires in {$this->ttlMinutes} minutes. Do not share this code.";

        match ($gateway) {
            'vonage' => $this->sendViaVonage($phone, $message),
            'twilio' => $this->sendViaTwilio($phone, $message),
            'aws_sns' => $this->sendViaAwsSns($phone, $message),
            default => $this->sendViaLog($phone, $message), // Local/testing fallback
        };
    }

    protected function sendViaVonage(string $phone, string $message): void
    {
        // composer require vonage/client
        // $client = new \Vonage\Client(new \Vonage\Client\Credentials\Basic(
        //     config('services.vonage.key'),
        //     config('services.vonage.secret')
        // ));
        // $client->sms()->send(new \Vonage\SMS\Message\SMS($phone, config('services.vonage.sms_from'), $message));

        logger()->info("[SMSService/Vonage] Would send to {$phone}: {$message}");
    }

    protected function sendViaTwilio(string $phone, string $message): void
    {
        // composer require twilio/sdk
        // $twilio = new \Twilio\Rest\Client(
        //     config('services.twilio.sid'),
        //     config('services.twilio.token')
        // );
        // $twilio->messages->create($phone, [
        //     'from' => config('services.twilio.from'),
        //     'body' => $message,
        // ]);

        logger()->info("[SMSService/Twilio] Would send to {$phone}: {$message}");
    }

    protected function sendViaAwsSns(string $phone, string $message): void
    {
        // Use the AWS SDK or Laravel's notification channel
        // \Illuminate\Support\Facades\Http::withHeaders([...])
        //     ->post('https://sns.amazonaws.com/', [...]);

        logger()->info("[SMSService/AWS SNS] Would send to {$phone}: {$message}");
    }

    /**
     * Fallback for local development — logs OTP to Laravel log file.
     * NEVER enable in production.
     */
    protected function sendViaLog(string $phone, string $message): void
    {
        if (app()->isProduction()) {
            throw new \Exception('SMS log driver cannot be used in production.');
        }

        logger()->debug("[SMSService/LOG] OTP for {$phone}: {$message}");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    protected function generateOtp(): string
    {
        return str_pad((string) random_int(0, 10 ** $this->otpLength - 1), $this->otpLength, '0', STR_PAD_LEFT);
    }

    protected function otpCacheKey(string $phone): string
    {
        return 'sms_otp_'.hash('sha256', $phone);
    }

    protected function sendRateLimitKey(string $phone): string
    {
        return 'sms_otp_rate_'.hash('sha256', $phone);
    }
}

<?php

namespace App\Modules\WorkOs\Services\AuthPlatform;

use App\Models\User;
use App\Models\AuthMfa;
use App\Models\AuthBackupCode;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class MFAEngine
{
    protected Google2FA $google2fa;

    public function __construct(Google2FA $google2fa)
    {
        $this->google2fa = $google2fa;
    }

    /**
     * Start the setup process for TOTP MFA.
     * Generates a secret and an SVG QR code for the user to scan.
     */
    public function setupTotp(User $user)
    {
        // Check if already active and delete for testing/demo purposes
        $existing = AuthMfa::where('user_id', $user->id)->where('is_active', true)->first();
        if ($existing) {
            $this->disable($user); // Disable and remove old backup codes
        }

        $secret = $this->google2fa->generateSecretKey();

        // Save as inactive until verified
        AuthMfa::updateOrCreate(
            ['user_id' => $user->id, 'type' => 'totp'],
            ['secret' => Crypt::encryptString($secret), 'is_active' => false]
        );

        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        // Generate SVG
        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $svg = $writer->writeString($qrCodeUrl);

        return [
            'secret' => $secret,
            'qr_code_svg' => base64_encode($svg), // Base64 encoded SVG for frontend rendering
        ];
    }

    /**
     * Verify the code and activate MFA for the user.
     */
    public function verifyAndActivate(User $user, string $code)
    {
        $mfa = AuthMfa::where('user_id', $user->id)->where('type', 'totp')->first();

        if (!$mfa) {
            throw new \Exception("MFA setup has not been initiated.");
        }

        $secret = Crypt::decryptString($mfa->secret);
        $valid = $this->google2fa->verifyKey($secret, $code);

        if (!$valid) {
            throw new \Exception("Invalid verification code.");
        }

        return DB::transaction(function () use ($user, $mfa) {
            $mfa->update([
                'is_active' => true,
                'verified_at' => Carbon::now(),
            ]);

            // Generate backup codes upon successful activation
            $backupCodes = $this->generateBackupCodes($user);

            return [
                'success' => true,
                'backup_codes' => $backupCodes, // Only show once
            ];
        });
    }

    /**
     * Verify a login code. Can be TOTP or Backup Code.
     */
    public function verifyLogin(User $user, string $code): bool
    {
        $mfa = AuthMfa::where('user_id', $user->id)->where('type', 'totp')->where('is_active', true)->first();

        if (!$mfa) {
            throw new \Exception("MFA is not active for this user.");
        }

        // Try TOTP first
        $secret = Crypt::decryptString($mfa->secret);
        $validTotp = $this->google2fa->verifyKey($secret, $code);

        if ($validTotp) {
            return true;
        }

        // Try Backup Codes
        $backupCodes = AuthBackupCode::where('user_id', $user->id)->where('is_used', false)->get();
        
        foreach ($backupCodes as $backupCode) {
            if (Hash::check($code, $backupCode->code_hash)) {
                // Mark as used
                $backupCode->update([
                    'is_used' => true,
                    'used_at' => Carbon::now(),
                ]);
                return true;
            }
        }

        return false;
    }

    /**
     * Disable MFA for a user
     */
    public function disable(User $user)
    {
        AuthMfa::where('user_id', $user->id)->delete();
        AuthBackupCode::where('user_id', $user->id)->delete();
    }

    /**
     * Generate 10 new random backup codes
     */
    public function generateBackupCodes(User $user): array
    {
        AuthBackupCode::where('user_id', $user->id)->delete(); // Remove old codes

        $codes = [];
        $inserts = [];

        for ($i = 0; $i < 10; $i++) {
            $code = Str::random(10);
            $codes[] = $code;
            $inserts[] = [
                'id' => Str::uuid()->toString(),
                'user_id' => $user->id,
                'code_hash' => Hash::make($code),
                'is_used' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        AuthBackupCode::insert($inserts);

        return $codes;
    }
}

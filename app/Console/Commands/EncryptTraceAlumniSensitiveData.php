<?php

namespace App\Console\Commands;

use App\Models\Tracer\ProfilAlumni;
use Illuminate\Console\Command;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

class EncryptTraceAlumniSensitiveData extends Command
{
    protected $signature = 'trace:encrypt-alumni-sensitive-data {--dry-run : Count plaintext values without updating data}';

    protected $description = 'Encrypt legacy plaintext NIK and NPWP values in Trace alumni profiles.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $checked = 0;
        $updated = 0;

        ProfilAlumni::query()
            ->select(['id', 'user_id', 'nik', 'npwp'])
            ->orderBy('id')
            ->chunkById(100, function ($profiles) use ($dryRun, &$checked, &$updated) {
                foreach ($profiles as $profile) {
                    $checked++;
                    $rawNik = $profile->getRawOriginal('nik');
                    $rawNpwp = $profile->getRawOriginal('npwp');
                    $payload = [];

                    if ($this->shouldEncrypt($rawNik)) {
                        $payload['nik'] = $rawNik;
                    }

                    if ($this->shouldEncrypt($rawNpwp)) {
                        $payload['npwp'] = $rawNpwp;
                    }

                    if ($payload === []) {
                        continue;
                    }

                    $updated++;

                    if (! $dryRun) {
                        $profile->forceFill($payload)->save();
                    }
                }
            });

        $this->info(($dryRun ? 'Would encrypt' : 'Encrypted')." {$updated} of {$checked} alumni profile records.");

        return self::SUCCESS;
    }

    private function shouldEncrypt(?string $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        try {
            Crypt::decryptString($value);

            return false;
        } catch (DecryptException) {
            return true;
        }
    }
}

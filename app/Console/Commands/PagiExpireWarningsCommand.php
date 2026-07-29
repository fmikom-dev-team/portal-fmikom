<?php

namespace App\Console\Commands;

use App\Models\Pagi\PagiNotification;
use App\Models\Pagi\PagiWarning;
use App\Models\Portal\PortalSetting;
use App\Models\User;
use Illuminate\Console\Command;

class PagiExpireWarningsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pagi:expire-warnings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis menonaktifkan Surat Peringatan (SP) PAGI yang telah melewati tanggal kedaluwarsa dan mengevaluasi status akun pengguna.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $expiredWarnings = PagiWarning::query()
            ->where('is_active', true)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        if ($expiredWarnings->isEmpty()) {
            $this->info('Tidak ada Surat Peringatan (SP) kedaluwarsa yang perlu diproses.');

            return Command::SUCCESS;
        }

        $affectedUserIds = $expiredWarnings->pluck('user_id')->unique();
        $expiredCount = 0;

        foreach ($expiredWarnings as $warning) {
            $warning->forceFill(['is_active' => false])->save();
            $expiredCount++;
        }

        $restoredUsersCount = 0;
        $maxAllowed = (int) (PortalSetting::query()->where('key', 'pagi_max_warnings_before_suspend')->value('value') ?? 3);

        foreach ($affectedUserIds as $userId) {
            $user = User::query()->find($userId);
            if (! $user || $user->is_active) {
                continue;
            }

            $activeCount = PagiWarning::query()
                ->where('user_id', $userId)
                ->where('is_active', true)
                ->count();

            if ($activeCount < $maxAllowed) {
                $user->forceFill(['is_active' => true])->save();
                $restoredUsersCount++;

                $user->notify(new PagiNotification(
                    type: 'system',
                    title: 'Status Akun Dipulihkan',
                    message: 'Akun Anda telah diaktifkan kembali karena Surat Peringatan (SP) Anda telah kedaluwarsa.',
                    avatar: null,
                    href: '/pagi'
                ));
            }
        }

        $this->info("Berhasil menonaktifkan {$expiredCount} SP kedaluwarsa dan memulihkan {$restoredUsersCount} akun pengguna.");

        return Command::SUCCESS;
    }
}

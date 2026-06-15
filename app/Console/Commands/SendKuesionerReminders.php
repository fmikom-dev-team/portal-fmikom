<?php

namespace App\Console\Commands;

use App\Mail\Trace\KuesionerReminder;
use App\Models\Tracer\Kuesioner;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendKuesionerReminders extends Command
{
    protected $signature = 'trace:kuesioner-reminder';
    protected $description = 'Send reminder emails to alumni who have not completed active kuesioners';

    public function handle(): int
    {
        $activeKuesioners = Kuesioner::where('is_active', true)
            ->where('status', 'published')
            ->where(function ($query) {
                $query->whereNull('date_selesai')
                    ->orWhere('date_selesai', '>=', now());
            })
            ->get();

        if ($activeKuesioners->isEmpty()) {
            $this->info('No active kuesioners found. Skipping reminders.');
            return self::SUCCESS;
        }

        $alumni = User::whereHas('alumniProfile')
            ->whereNotNull('email')
            ->get();

        $sent = 0;

        foreach ($activeKuesioners as $kuesioner) {
            // Get IDs of users who have already responded to this kuesioner
            $respondedUserIds = $kuesioner->responses()->pluck('user_id')->toArray();

            foreach ($alumni as $user) {
                // Skip if this user has already responded
                if (in_array($user->id, $respondedUserIds)) {
                    continue;
                }

                try {
                    Mail::to($user->email)->queue(
                        new KuesionerReminder(
                            $user->name ?? 'Alumni',
                            $kuesioner
                        )
                    );
                    $sent++;
                } catch (\Exception $e) {
                    $this->error("Failed to send reminder to {$user->email} for kuesioner '{$kuesioner->judul}': {$e->getMessage()}");
                }
            }
        }

        $this->info("Kuesioner reminders sent: {$sent} emails.");
        return self::SUCCESS;
    }
}

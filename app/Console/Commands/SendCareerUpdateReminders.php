<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\Trace\CareerUpdateReminderNotification;
use Illuminate\Console\Command;

class SendCareerUpdateReminders extends Command
{
    protected $signature = 'trace:career-reminder';

    protected $description = 'Send reminder emails and notifications to alumni who have not updated their career history in the last 1 year';

    public function handle(): int
    {
        $threshold = now()->subYear();

        $query = User::whereHas('alumniProfile', function ($query) use ($threshold) {
            $query->where(function ($q) use ($threshold) {
                $q->whereDoesntHave('careers')
                    ->where('updated_at', '<=', $threshold);
            })->orWhere(function ($q) use ($threshold) {
                $q->whereHas('careers')
                    ->whereDoesntHave('careers', function ($sub) use ($threshold) {
                        $sub->where('updated_at', '>', $threshold);
                    });
            });
        });

        $count = $query->count();

        if ($count === 0) {
            $this->info('No alumni found with outdated career profiles. Skipping reminders.');

            return self::SUCCESS;
        }

        $sent = 0;

        $query->chunkById(200, function ($users) use (&$sent) {
            foreach ($users as $user) {
                if (empty($user->email)) {
                    continue;
                }

                try {
                    $user->notify(new CareerUpdateReminderNotification);
                    $sent++;
                } catch (\Exception $e) {
                    $this->error("Failed to send career reminder to {$user->email}: {$e->getMessage()}");
                }
            }
        });

        $this->info("Career update reminders dispatched: {$sent} alumni notified.");

        return self::SUCCESS;
    }
}

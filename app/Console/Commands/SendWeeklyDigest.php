<?php

namespace App\Console\Commands;

use App\Mail\Trace\WeeklyJobDigest;
use App\Models\Tracer\Event;
use App\Models\Tracer\JobListing;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendWeeklyDigest extends Command
{
    protected $signature = 'trace:weekly-digest';

    protected $description = 'Send weekly job & event digest email to all alumni';

    public function handle(): int
    {
        $newJobs = JobListing::where('status', 'published')
            ->where('created_at', '>=', now()->subWeek())
            ->with('mitra')
            ->get();

        $newEvents = Event::where('status', 'published')
            ->where('created_at', '>=', now()->subWeek())
            ->get();

        if ($newJobs->isEmpty() && $newEvents->isEmpty()) {
            $this->info('No new jobs or events this week. Skipping digest.');

            return self::SUCCESS;
        }

        $weekNumber = now()->weekOfYear;
        $sent = 0;

        User::whereHas('alumniProfile')
            ->whereNotNull('email')
            ->chunkById(200, function ($alumni) use ($newJobs, $newEvents, $weekNumber, &$sent) {
                foreach ($alumni as $user) {
                    try {
                        Mail::to($user->email)->queue(
                            new WeeklyJobDigest(
                                $user->name ?? 'Alumni',
                                $newJobs,
                                $newEvents,
                                $weekNumber
                            )
                        );
                        $sent++;
                    } catch (\Exception $e) {
                        $this->error("Failed to send to {$user->email}: {$e->getMessage()}");
                    }
                }
            });

        $this->info("Weekly digest sent to {$sent} alumni.");

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Mail\Trace\EventReminder;
use App\Models\Tracer\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendEventReminders extends Command
{
    protected $signature = 'trace:event-reminder';
    protected $description = 'Send reminder emails for events happening tomorrow';

    public function handle(): int
    {
        $tomorrow = now()->addDay()->toDateString();

        $events = Event::where('status', 'published')
            ->whereDate('event_date', $tomorrow)
            ->with(['registeredUsers'])
            ->get();

        if ($events->isEmpty()) {
            $this->info('No events happening tomorrow. Skipping reminders.');
            return self::SUCCESS;
        }

        $sent = 0;

        foreach ($events as $event) {
            foreach ($event->registeredUsers as $user) {
                if (empty($user->email)) {
                    continue;
                }

                try {
                    Mail::to($user->email)->queue(
                        new EventReminder(
                            $user->name ?? 'Alumni',
                            $event
                        )
                    );
                    $sent++;
                } catch (\Exception $e) {
                    $this->error("Failed to send reminder to {$user->email} for event '{$event->title}': {$e->getMessage()}");
                }
            }
        }

        $this->info("Event reminders sent: {$sent} emails for {$events->count()} event(s).");
        return self::SUCCESS;
    }
}

<?php

namespace App\Notifications\Trace;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class JobApplicationSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected string $alumniName,
        protected string $jobTitle,
        protected int $jobId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $isAdmin = in_array($notifiable->user_type, ['admin', 'staff', 'super-admin']);

        return [
            'type' => 'job_application',
            'title' => 'Lamaran Baru',
            'message' => "{$this->alumniName} melamar lowongan \"{$this->jobTitle}\"",
            'icon' => 'briefcase',
            'href' => $isAdmin
                ? "/trace/admin/jobs/{$this->jobId}"
                : "/trace/open-job/jobs-listings/{$this->jobId}",
        ];
    }
}

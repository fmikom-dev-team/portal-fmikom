<?php

namespace App\Notifications\Trace;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class JobApplicationSubmitted extends Notification
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
        return [
            'type' => 'job_application',
            'title' => 'Lamaran Baru',
            'message' => "{$this->alumniName} melamar lowongan \"{$this->jobTitle}\"",
            'icon' => 'briefcase',
            'href' => "/trace/open-job/jobs-listings/{$this->jobId}",
        ];
    }
}

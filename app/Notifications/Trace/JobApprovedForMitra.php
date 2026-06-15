<?php

namespace App\Notifications\Trace;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class JobApprovedForMitra extends Notification
{
    use Queueable;

    public function __construct(
        private string $jobTitle,
        private int $jobId
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'job_approved',
            'title' => 'Lowongan Disetujui',
            'message' => "Lowongan \"{$this->jobTitle}\" telah disetujui dan dipublikasikan.",
            'icon' => 'check-circle',
            'href' => "/trace/open-job/jobs-listings/{$this->jobId}",
        ];
    }
}

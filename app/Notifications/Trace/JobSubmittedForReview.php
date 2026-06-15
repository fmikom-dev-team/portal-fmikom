<?php

namespace App\Notifications\Trace;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class JobSubmittedForReview extends Notification
{
    use Queueable;

    public function __construct(
        private string $jobTitle,
        private string $companyName,
        private int $jobId
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'job_review',
            'title' => 'Lowongan Perlu Review',
            'message' => "{$this->companyName} mengajukan lowongan: {$this->jobTitle}",
            'icon' => 'eye',
            'href' => "/trace/admin/jobs/{$this->jobId}",
        ];
    }
}

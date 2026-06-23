<?php

namespace App\Notifications\Trace;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewJobPosted extends Notification implements ShouldQueue
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
            'type' => 'new_job',
            'title' => 'Lowongan Baru',
            'message' => "{$this->companyName} membuka lowongan: {$this->jobTitle}",
            'icon' => 'briefcase',
            'href' => "/trace/jobs/{$this->jobId}",
        ];
    }
}

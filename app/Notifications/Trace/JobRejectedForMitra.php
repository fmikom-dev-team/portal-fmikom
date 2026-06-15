<?php

namespace App\Notifications\Trace;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class JobRejectedForMitra extends Notification
{
    use Queueable;

    public function __construct(
        private string $jobTitle,
        private int $jobId,
        private ?string $reason = null
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $message = "Lowongan \"{$this->jobTitle}\" ditolak.";
        if ($this->reason) {
            $message .= " Alasan: {$this->reason}";
        }
        return [
            'type' => 'job_rejected',
            'title' => 'Lowongan Ditolak',
            'message' => $message,
            'icon' => 'x-circle',
            'href' => "/trace/open-job/jobs-listings/{$this->jobId}",
        ];
    }
}

<?php

namespace App\Notifications\Trace;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JobRejectedForMitra extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $jobTitle,
        private int $jobId,
        private ?string $reason = null
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $companyName = $notifiable->mitraProfile->nama_perusahaan ?? 'Mitra';

        return (new MailMessage)
            ->subject('[Portal FMIKOM] Perubahan Status Lowongan Pekerjaan Anda')
            ->markdown('emails.trace.job-rejected-mitra', [
                'companyName' => $companyName,
                'jobTitle' => $this->jobTitle,
                'jobId' => $this->jobId,
                'reason' => $this->reason,
            ]);
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

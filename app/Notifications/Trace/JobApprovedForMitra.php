<?php

namespace App\Notifications\Trace;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JobApprovedForMitra extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $jobTitle,
        private int $jobId
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $companyName = $notifiable->mitraProfile?->nama_perusahaan ?? 'Mitra';

        return (new MailMessage)
            ->subject('[Portal FMIKOM] Lowongan Pekerjaan Anda Telah Disetujui')
            ->markdown('emails.trace.job-approved-mitra', [
                'companyName' => $companyName,
                'jobTitle' => $this->jobTitle,
                'jobId' => $this->jobId,
            ]);
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

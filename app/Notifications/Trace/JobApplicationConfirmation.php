<?php

namespace App\Notifications\Trace;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JobApplicationConfirmation extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected string $jobTitle,
        protected string $companyName,
        protected int $jobId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('[Portal FMIKOM] Konfirmasi Lamaran Pekerjaan Berhasil Dikirim')
            ->markdown('emails.trace.job-application-confirmation', [
                'userName' => $notifiable->name ?? 'Alumni',
                'jobTitle' => $this->jobTitle,
                'companyName' => $this->companyName,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'application_submitted',
            'title' => 'Lamaran Terkirim',
            'message' => "Lamaran Anda untuk posisi \"{$this->jobTitle}\" di \"{$this->companyName}\" telah berhasil dikirim.",
            'icon' => 'check-circle',
            'href' => "/trace/jobs/{$this->jobId}",
        ];
    }
}

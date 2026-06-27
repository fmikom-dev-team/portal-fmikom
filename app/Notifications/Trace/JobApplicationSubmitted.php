<?php

namespace App\Notifications\Trace;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
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
        $isAdmin = in_array($notifiable->user_type, ['admin', 'staff', 'super-admin']);
        return $isAdmin ? ['database'] : ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $companyName = $notifiable->mitraProfile?->nama_perusahaan ?? 'Mitra';

        return (new MailMessage)
            ->subject('[Portal FMIKOM] Ada Pelamar Baru untuk Lowongan Anda')
            ->markdown('emails.trace.job-application-submitted', [
                'companyName' => $companyName,
                'alumniName' => $this->alumniName,
                'jobTitle' => $this->jobTitle,
                'jobId' => $this->jobId,
            ]);
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

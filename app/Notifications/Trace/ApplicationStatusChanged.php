<?php

namespace App\Notifications\Trace;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected string $jobTitle,
        protected string $status,
        protected string $companyName,
        protected int $jobId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = match ($this->status) {
            'accepted' => '[Portal FMIKOM] Kabar Baik: Hasil Seleksi Lamaran Kerja Anda',
            'rejected' => '[Portal FMIKOM] Pengumuman Hasil Seleksi Lamaran Kerja',
            'reviewed' => '[Portal FMIKOM] Status Lamaran Kerja Anda Sedang Ditinjau',
            default => '[Portal FMIKOM] Pembaruan Status Lamaran Kerja',
        };

        return (new MailMessage)
            ->subject($subject)
            ->markdown('emails.trace.application-status', [
                'name' => $notifiable->name ?? 'Alumni',
                'jobTitle' => $this->jobTitle,
                'companyName' => $this->companyName,
                'status' => $this->status,
                'url' => config('app.url')."/trace/jobs/{$this->jobId}",
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $statusLabel = match ($this->status) {
            'accepted' => 'diterima',
            'rejected' => 'ditolak',
            'reviewed' => 'sedang ditinjau',
            default => $this->status,
        };

        $icon = match ($this->status) {
            'accepted' => 'check-circle',
            'rejected' => 'x-circle',
            'reviewed' => 'eye',
            default => 'briefcase',
        };

        return [
            'type' => 'application_status',
            'title' => 'Status Lamaran Diperbarui',
            'message' => "Lamaran Anda di \"{$this->jobTitle}\" ({$this->companyName}) telah {$statusLabel}",
            'icon' => $icon,
            'status' => $this->status,
            'href' => "/trace/jobs/{$this->jobId}",
        ];
    }
}

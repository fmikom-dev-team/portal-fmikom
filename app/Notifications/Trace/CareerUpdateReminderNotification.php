<?php

namespace App\Notifications\Trace;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CareerUpdateReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct() {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('[Portal FMIKOM] Pengingat Pembaruan Riwayat Karir Alumni')
            ->markdown('emails.trace.career-update-reminder', [
                'userName' => $notifiable->name ?? 'Alumni',
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'career_reminder',
            'title' => 'Perbarui Riwayat Karir',
            'message' => 'Anda belum memperbarui riwayat pekerjaan Anda selama lebih dari 1 tahun. Silakan perbarui status karir Anda.',
            'icon' => 'briefcase',
            'href' => '/trace/career',
        ];
    }
}

<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * RegistrationRejectedNotification — Sent when admin rejects a registration request.
 */
class RegistrationRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private string $rejectionReason;

    public function __construct(string $rejectionReason = 'Data pendaftaran tidak memenuhi kriteria.')
    {
        $this->rejectionReason = $rejectionReason;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Pendaftaran Akun FMIKOM Portal Ditolak')
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line('Terima kasih telah melakukan pendaftaran di Portal FMIKOM.')
            ->line('Setelah melakukan verifikasi dan review berkas/data pendaftaran, dengan menyesal kami informasikan bahwa pendaftaran akun Anda ditolak dengan alasan:')
            ->line('"'.$this->rejectionReason.'"')
            ->line('Jika Anda merasa hal ini adalah kekeliruan atau ingin melakukan pendaftaran ulang dengan data yang benar, silakan hubungi administrator program studi.')
            ->line('Terima kasih atas pengertian Anda.');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}

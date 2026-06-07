<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserApprovedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Akun SAHABAT Anda Telah Disetujui')
            ->greeting('Halo, ' . $notifiable->name . '!')
            ->line('Kabar baik! Pendaftaran akun Anda di sistem SAHABAT (Sistem Administrasi, Himpunan, dan Basis Aktivitas Terpadu) telah disetujui oleh Admin.')
            ->line('Sekarang Anda dapat mengakses dashboard dan fitur-fitur lainnya sesuai dengan hak akses Anda.')
            ->action('Login ke Dashboard', url('/login'))
            ->line('Jika Anda memiliki pertanyaan, silakan hubungi pengurus organisasi.')
            ->line('Terima kasih telah bergabung!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}

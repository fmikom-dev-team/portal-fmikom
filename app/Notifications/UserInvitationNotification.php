<?php

namespace App\Notifications;

use App\Models\UserInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public UserInvitation $invitation;

    public function __construct(UserInvitation $invitation)
    {
        $this->invitation = $invitation;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $acceptUrl = url('/user-invitations/accept?token='.$this->invitation->token);
        $name = trim($this->invitation->first_name.' '.$this->invitation->last_name) ?: 'Rekan';

        return (new MailMessage)
            ->subject('Undangan Bergabung ke Portal FMIKOM UNUGHA')
            ->greeting("Halo {$name},")
            ->line('Anda telah diundang oleh Administrator untuk bergabung ke **Portal FMIKOM UNUGHA**.')
            ->line('Silakan klik tombol di bawah untuk menyetel kata sandi dan mengaktifkan akun Anda:')
            ->action('Terima Undangan & Setel Password', $acceptUrl)
            ->line('Tautan undangan ini berlaku selama **7 hari**. Jika Anda merasa tidak pernah meminta undangan ini, silakan abaikan email ini.')
            ->salutation("Salam hangat,\nTim Portal FMIKOM UNUGHA");
    }
}

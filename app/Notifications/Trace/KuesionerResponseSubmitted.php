<?php

namespace App\Notifications\Trace;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class KuesionerResponseSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected string $alumniName,
        protected string $kuesionerTitle,
        protected int $kuesionerId,
        protected int $totalResponses,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'kuesioner_response',
            'title' => 'Respons Kuesioner Baru',
            'message' => "{$this->alumniName} mengisi kuesioner \"{$this->kuesionerTitle}\" (total: {$this->totalResponses} responden)",
            'icon' => 'clipboard-check',
            'href' => "/trace/admin/questionnaires/{$this->kuesionerId}/analytics-page",
        ];
    }
}

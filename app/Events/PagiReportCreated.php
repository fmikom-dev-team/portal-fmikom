<?php

namespace App\Events;

use App\Models\Pagi\PagiReport;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a new PagiReport is created.
 * Broadcasts to the private admin channel so all admins
 * receive a real-time notification via Reverb.
 */
class PagiReportCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly PagiReport $report,
        public readonly string $workTitle,
        public readonly string $reporterName,
        public readonly string $reporterHandle,
    ) {}

    /** Broadcast on the shared private admin channel */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('pagi.admin.reports')];
    }

    /** Event name on the client side: .PagiReportCreated */
    public function broadcastAs(): string
    {
        return 'PagiReportCreated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->report->id,
            'work_id' => $this->report->work_id,
            'work_title' => $this->workTitle,
            'reporter_name' => $this->reporterName,
            'reporter_handle' => $this->reporterHandle,
            'reason' => $this->report->reason,
            'created_at' => $this->report->created_at?->toISOString(),
        ];
    }
}

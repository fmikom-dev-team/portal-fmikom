<?php

namespace App\Events\Radar;

use App\Models\Radar\RadarDetection;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ThreatDetected implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public RadarDetection $detection;

    public function __construct(RadarDetection $detection)
    {
        $this->detection = $detection;
    }

    public function broadcastOn()
    {
        // For simplicity, broadcast on a private channel for admins
        return new PrivateChannel('radar.alerts');
    }

    public function broadcastWith()
    {
        // Load relationships to avoid N+1 and get human readable formats
        $this->detection->load(['protection', 'device']);
        
        return [
            'id' => $this->detection->id,
            'type' => $this->detection->detection_type,
            'severity' => $this->detection->severity,
            'risk_score' => $this->detection->risk_score,
            'action' => $this->detection->action_taken,
            'ip' => $this->detection->ip_address,
            'device' => $this->detection->device ? $this->detection->device->os . ' ' . $this->detection->device->browser : 'Unknown',
            'created_at' => $this->detection->created_at->format('M d, g:i A'),
            'created_at_human' => $this->detection->created_at->diffForHumans(),
        ];
    }
}

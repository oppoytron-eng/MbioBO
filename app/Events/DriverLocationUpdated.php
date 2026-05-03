<?php

namespace App\Events;

use App\Models\ApiCourseTrack;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DriverLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ApiCourseTrack $track;

    /**
     * Create a new event instance.
     */
    public function __construct(ApiCourseTrack $track)
    {
        $this->track = $track;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('course.' . $this->track->course_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'driver.location.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'course_id' => $this->track->course_id,
            'chauffeur_id' => $this->track->chauffeur_id,
            'latitude' => $this->track->latitude,
            'longitude' => $this->track->longitude,
            'bearing' => $this->track->bearing,
            'speed' => $this->track->speed,
            'recorded_at' => $this->track->recorded_at->toISOString(),
        ];
    }
}

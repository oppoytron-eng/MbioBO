<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CourseRequested implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $course;
    public $chauffeurs;

    /**
     * Create a new event instance.
     */
    public function __construct($course, $chauffeurs)
    {
        $this->course = $course;
        $this->chauffeurs = $chauffeurs;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        // Créer un canal pour chaque chauffeur concerné
        return $this->chauffeurs->map(function ($chauffeur) {
            return new PrivateChannel('chauffeur.' . $chauffeur->id);
        })->toArray();
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs()
    {
        return 'course.requested';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith()
    {
        return [
            'course' => [
                'id' => $this->course->id,
                'depart_latitude' => $this->course->depart_latitude,
                'depart_longitude' => $this->course->depart_longitude,
                'arrivee_latitude' => $this->course->arrivee_latitude,
                'arrivee_longitude' => $this->course->arrivee_longitude,
                'prix_estime' => $this->course->prix_estime,
                'distance_meters' => $this->course->distance_meters,
                'client_snapshot' => $this->course->client_snapshot,
                'created_at' => $this->course->created_at,
            ],
        ];
    }
}

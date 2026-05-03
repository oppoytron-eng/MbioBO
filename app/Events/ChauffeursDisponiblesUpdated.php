<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChauffeursDisponiblesUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $courseId;
    public $chauffeurs;

    /**
     * Create a new event instance.
     */
    public function __construct($courseId, $chauffeurs)
    {
        $this->courseId = $courseId;
        $this->chauffeurs = $chauffeurs;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return [
            new PrivateChannel('course.' . $this->courseId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs()
    {
        return 'chauffeurs.disponibles.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith()
    {
        return [
            'course_id' => $this->courseId,
            'chauffeurs' => $this->chauffeurs->map(function ($chauffeur) {
                return [
                    'id' => $chauffeur->id,
                    'name' => $chauffeur->name,
                    'telephone' => $chauffeur->telephone,
                    'latitude' => $chauffeur->latitude ?? 0,
                    'longitude' => $chauffeur->longitude ?? 0,
                    'est_actif' => $chauffeur->est_actif,
                    'distance_km' => $chauffeur->distance_km ?? 0,
                ];
            }),
        ];
    }
}

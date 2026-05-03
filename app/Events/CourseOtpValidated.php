<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CourseOtpValidated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $courseId;
    public $chauffeurId;
    public $validatedAt;

    /**
     * Create a new event instance.
     */
    public function __construct($courseId, $chauffeurId)
    {
        $this->courseId = $courseId;
        $this->chauffeurId = $chauffeurId;
        $this->validatedAt = now();
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
        return 'course.otp.validated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith()
    {
        return [
            'course_id' => $this->courseId,
            'chauffeur_id' => $this->chauffeurId,
            'validated_at' => $this->validatedAt->toISOString(),
            'message' => 'Code OTP validé - La course commence !'
        ];
    }
}

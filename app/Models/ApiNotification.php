<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiNotification extends Model
{
    protected $table = 'api_notifications';

    protected $casts = [
        'payload' => 'array',
        'distance_meters' => 'decimal:2',
        'countdown_active' => 'boolean',
        'client_info' => 'array',
    ];

    protected $fillable = [
        'course_id',
        'type',
        'message',
        'statut',
        'payload',
        'distance_meters',
        'sound',
        'countdown_active',
        'countdown_seconds',
        'client_info',
    ];

    public function course()
    {
        return $this->belongsTo(ApiCourse::class, 'course_id');
    }

    public function chauffeurs()
    {
        return $this->hasMany(ApiNotificationChauffeur::class, 'notification_id');
    }
}

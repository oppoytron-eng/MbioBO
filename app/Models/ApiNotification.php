<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiNotification extends Model
{
    protected $table = 'api_notifications';

    protected $fillable = [
        'course_id',
        'type',
        'message',
        'statut',
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

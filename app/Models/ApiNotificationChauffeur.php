<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiNotificationChauffeur extends Model
{
    protected $table = 'api_notifications_chauffeurs';

    protected $fillable = [
        'notification_id',
        'chauffeur_id',
        'statut',
        'expire_le',
        'countdown_active',
        'countdown_started_at',
        'countdown_expired_at',
    ];

    protected $casts = [
        'expire_le' => 'datetime',
        'countdown_active' => 'boolean',
        'countdown_started_at' => 'datetime',
        'countdown_expired_at' => 'datetime',
    ];

    public function notification()
    {
        return $this->belongsTo(ApiNotification::class, 'notification_id');
    }

    public function chauffeur()
    {
        return $this->belongsTo(User::class, 'chauffeur_id');
    }
}

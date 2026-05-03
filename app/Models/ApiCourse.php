<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiCourse extends Model
{
    protected $table = 'api_courses';

    protected $fillable = [
        'client_id',
        'chauffeur_id',
        'depart_latitude',
        'depart_longitude',
        'arrivee_latitude',
        'arrivee_longitude',
        'prix_estime',
        'prix_final',
        'mode_paiement',
        'statut',
        'est_paye',
        'ride_otp',
        'otp_expires_at',
        'otp_verified_at',
        'countdown_seconds',
        'countdown_started_at',
        'distance_meters',
        'client_snapshot',
        'metadata',
    ];

    protected $casts = [
        'depart_latitude' => 'float',
        'depart_longitude' => 'float',
        'arrivee_latitude' => 'float',
        'arrivee_longitude' => 'float',
        'prix_estime' => 'decimal:2',
        'prix_final' => 'decimal:2',
        'est_paye' => 'boolean',
        'otp_expires_at' => 'datetime',
        'otp_verified_at' => 'datetime',
        'countdown_seconds' => 'integer',
        'countdown_started_at' => 'datetime',
        'client_snapshot' => 'array',
        'metadata' => 'array',
        'distance_meters' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function chauffeur()
    {
        return $this->belongsTo(User::class, 'chauffeur_id');
    }

    public function chauffeurProfile()
    {
        return $this->hasOneThrough(
            Chauffeur::class,
            User::class,
            'id', // Clé dans users
            'utilisateur_id', // Clé dans chauffeurs
            'chauffeur_id', // Clé locale dans api_courses
            'id' // Clé locale dans users
        );
    }

    public function notifications()
    {
        return $this->hasMany(ApiNotification::class, 'course_id');
    }

    public function tracks()
    {
        return $this->hasMany(ApiCourseTrack::class, 'course_id');
    }
}

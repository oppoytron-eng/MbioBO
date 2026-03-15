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
    ];

    protected $casts = [
        'depart_latitude' => 'float',
        'depart_longitude' => 'float',
        'arrivee_latitude' => 'float',
        'arrivee_longitude' => 'float',
        'prix_estime' => 'decimal:2',
        'prix_final' => 'decimal:2',
        'est_paye' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function chauffeur()
    {
        return $this->belongsTo(User::class, 'chauffeur_id');
    }

    public function notifications()
    {
        return $this->hasMany(ApiNotification::class, 'course_id');
    }
}

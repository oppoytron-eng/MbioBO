<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiCourseTrack extends Model
{
    protected $table = 'api_course_tracks';

    protected $fillable = [
        'course_id',
        'chauffeur_id',
        'latitude',
        'longitude',
        'latitude_accuracy',
        'longitude_accuracy',
        'bearing',
        'speed',
        'recorded_at',
        'meta',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'latitude_accuracy' => 'decimal:4',
        'longitude_accuracy' => 'decimal:4',
        'bearing' => 'decimal:2',
        'speed' => 'decimal:2',
        'recorded_at' => 'datetime',
        'meta' => 'array',
    ];

    public function course()
    {
        return $this->belongsTo(ApiCourse::class, 'course_id');
    }

    public function chauffeur()
    {
        return $this->belongsTo(User::class, 'chauffeur_id');
    }
}

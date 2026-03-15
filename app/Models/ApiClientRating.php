<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiClientRating extends Model
{
    protected $table = 'api_client_ratings';

    protected $fillable = [
        'course_id',
        'driver_id',
        'client_id',
        'rating',
        'comment',
        'meta',
    ];

    protected $casts = [
        'rating' => 'integer',
        'meta' => 'array',
    ];

    public function course()
    {
        return $this->belongsTo(ApiCourse::class, 'course_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}

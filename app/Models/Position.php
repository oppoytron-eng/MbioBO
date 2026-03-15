<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $table = 'positions';
    public $timestamps = false;
    protected $guarded = [];

    public function course()
    {
        return $this->belongsTo(Course::class, 'courses_id');
    }

    public function chauffeur()
    {
        return $this->belongsTo(Chauffeur::class, 'chauffeur_id');
    }
}

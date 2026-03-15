<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

    class Evaluation extends Model
{
    protected $table = 'evaluations';
    public $timestamps = false;
    protected $fillable = [
        'course_id', 'evaluateur_id', 'evalue_id',
        'note', 'commentaire', 'evaluateur_role', 'dateHeure'
    ];

    public function course() {
        return $this->belongsTo(Course::class, 'course_id');
    }
    public function evaluateur() {
        return $this->belongsTo(Utilisateur::class, 'evaluateur_id');
    }
    public function evalue() {
        return $this->belongsTo(Utilisateur::class, 'evalue_id');
    }
}

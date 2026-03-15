<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

    class Notification extends Model
{
    protected $table = 'notifications';
    public $timestamps = false;
    protected $fillable = [
        'destinataire_id', 'course_id', 'type',
        'message', 'statut', 'envoye_le'
    ];

    public function destinataire() {
        return $this->belongsTo(Utilisateur::class, 'destinataire_id');
    }
    public function course() {
        return $this->belongsTo(Course::class, 'course_id');
    }
}

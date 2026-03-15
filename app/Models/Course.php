<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

   class Course extends Model
{
    public $incrementing = false;
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    
    protected $table = 'courses';
    public $timestamps = false;
    protected $fillable = ['client_id', 'chauffeur_id', 'statut', 'adresse_depart', 'lat_depart', 'lng_depart', 'adresse_arrivee', 'lat_arrivee', 'lng_arrivee',
        'distance_km', 'prix_estime', 'prix_final', 'est_paye', 'modePaiement', 'demande_le', 'termine_le', 'est_annule', 'est_terminee'];
    protected $casts = [
        'est_annule' => 'bool',
        'est_terminee' => 'bool',
    ];

    public function client() {
        return $this->belongsTo(Client::class, 'client_id');
    }
    public function chauffeur() {
        return $this->belongsTo(Chauffeur::class, 'chauffeur_id');
    }
    public function positions() {
        return $this->hasMany(Position::class, 'courses_id')->orderBy('timestamp');
    }
    public function evaluations() {
        return $this->hasMany(Evaluation::class, 'course_id');
    }
    public function notifications() {
        return $this->hasMany(Notification::class, 'course_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

    class Vehicule extends Model
{
    protected $table = 'vehicules';
    protected $primaryKey='id';
    protected $fillable = ['chauffeur_id', 'marque', 'modele','immatriculation', 'couleur', 'annee'];

    public function chauffeur() {
        return $this->belongsTo(Chauffeur::class, 'chauffeur_id');
    }
}

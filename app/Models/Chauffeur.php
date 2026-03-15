<?php

namespace App\Models;

use App\Models\ChauffeurDocument;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Chauffeur extends Model
{
    use SoftDeletes;
    use HasUuids;
    protected $table = 'chauffeurs';
    protected $primaryKey = 'id';
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'utilisateur_id',
        'numero_permis',
        'statut',
        'note_moyenne',
        'solde_total',
        'lat_actuelle',
        'lng_actuelle',
        'est_actif',
        'statut_operationnel',
        'etat_documents',
        'nb_courses',
    ];
    protected $casts = [
        'est_actif' => 'bool',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class,'utilisateur_id');
    }
    public function vehicule()
    {
        return $this->hasOne(Vehicule::class,'chauffeur_id');
    }
    public function courses()
    {
        return $this->hasMany(Course::class,'chauffeur_id');
    }
    public function positions()
    {
        return $this->hasMany(Position::class,'chauffeur_id');
    }
    public function documents()
    {
        return $this->hasMany(ChauffeurDocument::class,'chauffeur_id');
    }
}

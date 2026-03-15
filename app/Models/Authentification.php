<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

   class Authentification extends Model
{
    protected $table = 'authentifications';
    public $timestamps = false;
    protected $fillable = [
        'utilisateur_id', 'tentativesEchouees', 'maxTentatives',
        'dateVerrouillage', 'codeOTP', 'dateExpirationOTP'
    ];

    public function utilisateur() {
        return $this->belongsTo(Utilisateur::class, 'utilisateur_id');
    }
}

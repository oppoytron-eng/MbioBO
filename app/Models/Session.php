<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

   class Session extends Model
{
    protected $table = 'sessions';
    public $timestamps = false;
    protected $fillable = [
        'utilisateur_id', 'token', 'dateCreation',
        'dateExpiration', 'estActif', 'adresseIP', 'appareil'
    ];

    public function utilisateur() {
        return $this->belongsTo(Utilisateur::class, 'utilisateur_id');
    }
}

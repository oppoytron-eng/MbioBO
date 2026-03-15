<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSession extends Model
{
    protected $table = 'user_sessions';
    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'utilisateur_id',
        'token',
        'dateCreation',
        'dateExpiration',
        'estActif',
        'adresseIP',
        'appareil',
    ];

    protected $casts = [
        'estActif' => 'boolean',
        'dateCreation' => 'datetime',
        'dateExpiration' => 'datetime',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'utilisateur_id');
    }
}

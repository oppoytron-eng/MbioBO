<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChauffeurStatus extends Model
{
    protected $table = 'chauffeur_status';

    protected $fillable = [
        'user_id',
        'statut',
        'statut_operationnel',
        'last_seen',
    ];

    protected $casts = [
        'last_seen' => 'datetime',
    ];

    /**
     * Relation vers l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mettre à jour le statut de connexion
     */
    public static function updateStatus($userId, $statut)
    {
        return self::updateOrCreate(
            ['user_id' => $userId],
            [
                'statut' => $statut,
                'last_seen' => now(),
            ]
        );
    }
}

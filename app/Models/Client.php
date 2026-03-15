<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;
    protected $table='clients';
    protected $primaryKey='id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable=['utilisateur_id','note_moyenne','nb_courses','est_actif'];
    protected $casts = [
        'note_moyenne' => 'float',
        'nb_courses' => 'int',
        'est_actif' => 'bool',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'utilisateur_id');
    }
    public function courses()
    {
        return $this->hasMany(Course::class, 'client_id');
    }
    public function notifications()
    {
        return $this->hasMany(Notification::class,'destinataire_id');
    }
}

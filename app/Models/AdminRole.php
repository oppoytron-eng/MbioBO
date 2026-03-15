<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminRole extends Model
{
    protected $table = 'admin_roles';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['name', 'description', 'guard'];

    public function utilisateurs()
    {
        return $this->belongsToMany(Utilisateur::class, 'admin_role_utilisateur', 'admin_role_id', 'utilisateur_id')->withPivot('assigned_at');
    }
}

<?php

namespace App\Models;

use App\Models\Chauffeur;
use Illuminate\Database\Eloquent\Model;

class ChauffeurDocument extends Model
{
    protected $table = 'chauffeur_documents';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['chauffeur_id', 'type', 'status', 'path', 'notes', 'uploaded_at', 'reviewed_at'];
    public $timestamps = true;

    public function chauffeur()
    {
        return $this->belongsTo(Chauffeur::class, 'chauffeur_id');
    }
}

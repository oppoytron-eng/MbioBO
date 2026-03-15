<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiDriverDocument extends Model
{
    protected $table = 'api_driver_documents';

    protected $fillable = [
        'chauffeur_id',
        'type',
        'status',
        'path',
        'notes',
        'uploaded_at',
        'reviewed_at',
        'meta',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'meta' => 'array',
    ];

    public function chauffeur()
    {
        return $this->belongsTo(User::class, 'chauffeur_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Chauffeur;

class WithdrawalRequest extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'withdrawal_requests';
    public $timestamps = false;

    protected $fillable = [
        'chauffeur_id',
        'amount',
        'status',
        'note',
        'processed_by',
        'requested_at',
        'processed_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function chauffeur()
    {
        return $this->belongsTo(Chauffeur::class);
    }
}

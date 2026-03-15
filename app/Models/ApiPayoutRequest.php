<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiPayoutRequest extends Model
{
    protected $table = 'api_payout_requests';

    protected $fillable = [
        'wallet_id',
        'amount',
        'status',
        'note',
        'processed_by',
        'requested_at',
        'processed_at',
        'meta',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
        'meta' => 'array',
    ];

    public function wallet()
    {
        return $this->belongsTo(ApiWallet::class, 'wallet_id');
    }
}

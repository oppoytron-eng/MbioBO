<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiWallet extends Model
{
    protected $table = 'api_wallets';

    protected $fillable = [
        'user_id',
        'balance',
        'meta',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(ApiWalletTransaction::class, 'wallet_id');
    }

    public function payoutRequests()
    {
        return $this->hasMany(ApiPayoutRequest::class, 'wallet_id');
    }
}

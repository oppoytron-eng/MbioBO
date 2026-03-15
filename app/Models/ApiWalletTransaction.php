<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiWalletTransaction extends Model
{
    protected $table = 'api_wallet_transactions';

    protected $fillable = [
        'wallet_id',
        'type',
        'amount',
        'source',
        'meta',
        'course_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'meta' => 'array',
    ];

    public function wallet()
    {
        return $this->belongsTo(ApiWallet::class, 'wallet_id');
    }

    public function course()
    {
        return $this->belongsTo(ApiCourse::class, 'course_id');
    }
}

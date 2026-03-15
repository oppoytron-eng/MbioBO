<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'telephone',
        'email',
        'password',
        'role',
        'photo_url',
        'est_actif',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'est_actif' => 'boolean',
        'password' => 'hashed',
    ];

    public function apiWallet()
    {
        return $this->hasOne(ApiWallet::class, 'user_id');
    }

    public function driverDocuments()
    {
        return $this->hasMany(ApiDriverDocument::class, 'chauffeur_id');
    }

    public function walletTransactions()
    {
        return $this->hasManyThrough(
            ApiWalletTransaction::class,
            ApiWallet::class,
            'user_id',
            'wallet_id',
            'id',
            'id'
        );
    }

    public function payoutRequests()
    {
        return $this->hasManyThrough(
            ApiPayoutRequest::class,
            ApiWallet::class,
            'user_id',
            'wallet_id',
            'id',
            'id'
        );
    }

    public function clientRatings()
    {
        return $this->hasMany(ApiClientRating::class, 'client_id');
    }

    public function driverRatings()
    {
        return $this->hasMany(ApiClientRating::class, 'driver_id');
    }
}

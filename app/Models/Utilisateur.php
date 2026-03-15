<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class Utilisateur extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    protected $table = 'utilisateurs';
    protected $primaryKey = 'utilisateur_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'utilisateur_id',
        'nom',
        'prenom',
        'email',
        'telephone',
        'mot_de_passe',
        'role',
        'est_actif',
        'statut',
        'otp_code',
        'otp_expires_at',
    ];
    protected $hidden = ['mot_de_passe'];
    protected $casts = [
        'est_actif' => 'boolean',
        'otp_expires_at' => 'datetime',
    ];

    public function chauffeur() {
        return $this->hasOne(Chauffeur::class, 'utilisateur_id');
    }
    public function client() {
        return $this->hasOne(Client::class, 'utilisateur_id');
    }
    public function admin() {
        return $this->hasOne(Admin::class, 'utilisateur_id');
    }
    public function sessions() {
        return $this->hasMany(Session::class, 'utilisateur_id');
    }
    public function authentification() {
        return $this->hasOne(Authentification::class, 'utilisateur_id');
    }
    public function notifications() {
        return $this->hasMany(Notification::class, 'destinataire_id');
    }
    public function getAuthPassword()
    {
        return $this->mot_de_passe;
    }

    public function adminRoles()
    {
        return $this->belongsToMany(AdminRole::class, 'admin_role_utilisateur', 'utilisateur_id', 'admin_role_id')->withPivot('assigned_at');
    }

    public function hasAdminRole(string $role): bool
    {
        return $this->adminRoles->contains('name', $role);
    }

    public function generateOtp(int $minutes = 10): string
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->otp_code = Hash::make($code);
        $this->otp_expires_at = now()->addMinutes($minutes);
        $this->save();

        return $code;
    }

    public function otpIsValid(string $code): bool
    {
        return $this->otp_code
            && $this->otp_expires_at
            && now()->lt($this->otp_expires_at)
            && Hash::check($code, $this->otp_code);
    }

    public function clearOtp(): void
    {
        $this->otp_code = null;
        $this->otp_expires_at = null;
        $this->save();
    }
}

<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Utilisateur extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'utilisateurs';
    protected $primaryKey = 'id_user';
    public $timestamps = true;

    protected $fillable = [
    'nom',
    'prenom', 
    'email',
    'mot_de_passe',
    'role',
    'est_actif',
    'telephone',
    'created_by',   // ← ajoute ça
];

    protected $hidden = [
        'mot_de_passe',
    ];

    protected $casts = [
        'est_actif' => 'boolean',
    ];

    public function getAuthPassword()
    {
        return $this->mot_de_passe;
    }

    // ✅ Relations
    public function etudiant()
    {
        return $this->hasOne(Etudiant::class, 'id_user', 'id_user');
    }

    public function encadrant()
    {
        return $this->hasOne(Encadrant::class, 'id_user', 'id_user');
    }

    public function adminEcole()
    {
        return $this->hasOne(AdminEcole::class, 'id_user', 'id_user');
    }

    public function adminPlateforme()
    {
        return $this->hasOne(AdminPlateforme::class, 'id_user', 'id_user');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'id_user', 'id_user');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Etudiant extends Model
{
    protected $table = 'etudiants';
    protected $primaryKey = 'id_user';
    public $incrementing = false;
    public $timestamps = true;

    protected $fillable = [
        'id_user',
        'matricule', // ✅ Ajouté
        'id_filiere'
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_user', 'id_user');
    }

    public function filiere()
    {
        return $this->belongsTo(Filiere::class, 'id_filiere', 'id_filiere');
    }

    public function encadreurs()
    {
        return $this->hasMany(Encadreur::class, 'id_etudiant', 'id_user');
    }

    public function memoires()
    {
        return $this->belongsToMany(
            Memoire::class,
            'encadreur',
            'id_etudiant',
            'id_memoire',
            'id_user',
            'id_memoire'
        );
    }

    public function versions()
    {
        return $this->hasMany(Version::class, 'id_etudiant', 'id_user');
    }
}
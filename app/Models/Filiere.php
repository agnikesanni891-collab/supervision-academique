<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Filiere extends Model
{
    protected $table = 'filieres';
    protected $primaryKey = 'id_filiere';

    protected $fillable = [
        'libelle_filiere',
        'id_etablissement',
    ];

    public function etablissement()
    {
        return $this->belongsTo(Etablissement::class, 'id_etablissement', 'id_etablissement');
    }

    public function etudiants()
    {
        return $this->hasMany(Etudiant::class, 'id_filiere', 'id_filiere');
    }

    public function memoires()
    {
        return $this->hasMany(Memoire::class, 'id_filiere', 'id_filiere');
    }
}
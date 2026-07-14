<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Etablissement extends Model
{
    protected $table = 'etablissements';
    protected $primaryKey = 'id_etablissement';

    protected $fillable = [
        'nom_etablissement',
        'ville',
    ];

    public function filieres()
    {
        return $this->hasMany(Filiere::class, 'id_etablissement', 'id_etablissement');
    }
}
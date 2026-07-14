<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Encadreur extends Model
{
    protected $table = 'encadreur';
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = null;

    protected $fillable = [
        'id_memoire',
        'id_etudiant',
        'id_encadrant'
    ];

    public function memoire()
    {
        return $this->belongsTo(Memoire::class, 'id_memoire', 'id_memoire');
    }

    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class, 'id_etudiant', 'id_user');
    }

    public function encadrant()
    {
        return $this->belongsTo(Encadrant::class, 'id_encadrant', 'id_user');
    }
}
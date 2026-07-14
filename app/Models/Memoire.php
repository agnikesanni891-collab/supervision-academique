<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ChecklistItem;
class Memoire extends Model
{
    protected $table = 'memoires';
    protected $primaryKey = 'id_memoire';
    public $timestamps = true;

    protected $fillable = [
        'titre', 'statut', 'id_filiere', 'id_cycle'
    ];

    public function filiere()
    {
        return $this->belongsTo(Filiere::class, 'id_filiere', 'id_filiere');
    }
public function cycle()
{
    return $this->belongsTo(Cycle::class, 'id_cycle', 'id_cycle');
}
    public function encadreurs()
    {
        return $this->hasMany(Encadreur::class, 'id_memoire', 'id_memoire');
    }

    public function etudiants()
    {
        return $this->belongsToMany(
            Etudiant::class,
            'encadreur',
            'id_memoire',
            'id_etudiant',
            'id_memoire',
            'id_user'
        );
    }

    public function encadrants()
    {
        return $this->belongsToMany(
            Encadrant::class,
            'encadreur',
            'id_memoire',
            'id_encadrant',
            'id_memoire',
            'id_user'
        );
    }

    public function versions()
    {
        return $this->hasMany(VersionMemoire::class, 'id_memoire', 'id_memoire')
                    ->orderBy('numero_version', 'asc');
    }

    public function derniereVersion()
    {
        return $this->hasOne(VersionMemoire::class, 'id_memoire', 'id_memoire')
                    ->orderBy('numero_version', 'desc');
    }
    public function checklistItems()
{
    return $this->hasMany(ChecklistItem::class, 'id_memoire', 'id_memoire');
}
}
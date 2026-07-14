<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VersionMemoire extends Model
{
    protected $table = 'versions';
    protected $primaryKey = 'id_version';

    protected $fillable = [
        'numero_version',
        'url_fichier',
        'public_id_cloudinary',
        'taille_fichier',
        'statut_version',
        'texte_extrait',
        'id_memoire',
        'id_etudiant',
        'date_depot',
    ];

    const STATUTS = [
        'soumis',
        'accepte',
        'rejete',
    ];

    public function memoire()
    {
        return $this->belongsTo(Memoire::class, 'id_memoire', 'id_memoire');
    }

    public function etudiant()
    {
        return $this->belongsTo(Utilisateur::class, 'id_etudiant', 'id_user');
    }

    public function annotations()
    {
        return $this->hasMany(Annotation::class, 'id_version', 'id_version');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'id_version', 'id_version');
    }
}
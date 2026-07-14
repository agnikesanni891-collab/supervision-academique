<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Annotation extends Model
{
    protected $table      = 'annotations';
    protected $primaryKey = 'id_annotation';

    protected $fillable = [
        'page',
        'position_x',
        'position_y',
        'largeur',
        'hauteur',
        'texte_surligne',
        'texte_commentaire',
        'rects',
        'id_version',
        'id_encadrant',
        'date_annotation',
    ];

    public function version()
    {
        return $this->belongsTo(VersionMemoire::class, 'id_version', 'id_version');
    }

    public function encadrant()
    {
        return $this->belongsTo(Utilisateur::class, 'id_encadrant', 'id_user');
    }
}
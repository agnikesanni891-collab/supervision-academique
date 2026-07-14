<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Encadrant extends Model
{
    protected $table = 'encadrants';
    protected $primaryKey = 'id_user';
    public $incrementing = false;

    protected $fillable = [
        'id_user',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_user', 'id_user');
    }

    public function memoires()
    {
        return $this->hasMany(Memoire::class, 'id_encadrant', 'id_user');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminEcole extends Model
{
    protected $table = 'adminecole';
    protected $primaryKey = 'id_user';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['id_user', 'id_etablissement'];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_user', 'id_user');
    }

    public function etablissement()
    {
        return $this->belongsTo(Etablissement::class, 'id_etablissement', 'id_etablissement');
    }
}
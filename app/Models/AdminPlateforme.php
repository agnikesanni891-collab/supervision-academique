<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminPlateforme extends Model
{
    protected $table = 'adminplateforme';
    protected $primaryKey = 'id_user';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['id_user'];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_user', 'id_user');
    }
}
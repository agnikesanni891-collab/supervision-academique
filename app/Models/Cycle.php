<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cycle extends Model
{
    protected $primaryKey = 'id_cycle';

    protected $fillable = ['libelle'];

    public function memoires()
    {
        return $this->hasMany(Memoire::class, 'id_cycle', 'id_cycle');
    }
}
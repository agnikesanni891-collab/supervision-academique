<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'id_notification';

    protected $fillable = [
        'type_evenement',
        'message',
        'est_lue',
        'id_user',
        'id_version',
        'date_envoi',
    ];

    const TYPES = [
        'depot',
        'annotation',
        'acceptation',
        'rejet',
        'soutenance',
    ];

    public function destinataire()
    {
        return $this->belongsTo(Utilisateur::class, 'id_user', 'id_user');
    }

    public function version()
    {
        return $this->belongsTo(VersionMemoire::class, 'id_version', 'id_version');
    }
}
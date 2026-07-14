<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecklistItem extends Model
{
    use HasFactory;

    protected $table = 'checklist_items';
    protected $primaryKey = 'id_item';

    protected $fillable = [
        'id_memoire',
        'libelle',
        'categorie',
        'ordre',
        'est_complete',
        'date_completion',
        'complete_par',
        'commentaire',
    ];

    protected $casts = [
        'est_complete'     => 'boolean',
        'date_completion'  => 'datetime',
    ];

    // ── Relations ──────────────────────────────────────────────────────────────

    public function memoire()
    {
        return $this->belongsTo(Memoire::class, 'id_memoire', 'id_memoire');
    }

    public function completePar()
    {
        return $this->belongsTo(Utilisateur::class, 'complete_par', 'id_user');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeCompletes($query)
    {
        return $query->where('est_complete', true);
    }

    public function scopeEnAttente($query)
    {
        return $query->where('est_complete', false);
    }
}
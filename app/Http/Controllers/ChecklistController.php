<?php

namespace App\Http\Controllers;

use App\Models\ChecklistItem;
use App\Models\Memoire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChecklistController extends Controller
{
    // ── Étapes par défaut créées automatiquement pour chaque nouveau mémoire ──
    private array $etapesParDefaut = [
        ['libelle' => 'Page de titre et informations générales complétées',  'categorie' => 'redaction',     'ordre' => 1],
        ['libelle' => 'Introduction rédigée',                                'categorie' => 'redaction',     'ordre' => 2],
        ['libelle' => 'Problématique clairement définie',                    'categorie' => 'methodologie',  'ordre' => 3],
        ['libelle' => 'Revue de littérature complète',                       'categorie' => 'redaction',     'ordre' => 4],
        ['libelle' => 'Méthodologie détaillée',                              'categorie' => 'methodologie',  'ordre' => 5],
        ['libelle' => 'Développement / Résultats rédigés',                   'categorie' => 'redaction',     'ordre' => 6],
        ['libelle' => 'Conclusion et perspectives rédigées',                 'categorie' => 'redaction',     'ordre' => 7],
        ['libelle' => 'Bibliographie conforme aux normes',                   'categorie' => 'validation',    'ordre' => 8],
        ['libelle' => 'Annexes ajoutées si nécessaire',                      'categorie' => 'validation',    'ordre' => 9],
        ['libelle' => 'Mise en forme et orthographe vérifiées',              'categorie' => 'validation',    'ordre' => 10],
        ['libelle' => 'Version finale approuvée par l\'encadrant',           'categorie' => 'validation',    'ordre' => 11],
        ['libelle' => 'Prêt pour la soutenance',                             'categorie' => 'validation',    'ordre' => 12],
    ];

    // ── GET /api/memoires/{id}/checklist ──────────────────────────────────────
    // Retourne toutes les étapes + taux de progression
    public function index($id_memoire)
    {
        $memoire = Memoire::findOrFail($id_memoire);

        // Si aucune étape n'existe encore, on les crée automatiquement
        if ($memoire->checklistItems()->count() === 0) {
            $this->creerEtapesParDefaut($id_memoire);
        }

        $items = $memoire->checklistItems()->orderBy('ordre')->get();

        $total     = $items->count();
        $completes = $items->where('est_complete', true)->count();
        $taux      = $total > 0 ? round(($completes / $total) * 100) : 0;

        // Regrouper par catégorie
        $parCategorie = $items->groupBy('categorie')->map(function ($groupe) {
            return [
                'items'      => $groupe->values(),
                'completes'  => $groupe->where('est_complete', true)->count(),
                'total'      => $groupe->count(),
            ];
        });

        return response()->json([
            'items'         => $items,
            'par_categorie' => $parCategorie,
            'progression'   => [
                'total'     => $total,
                'completes' => $completes,
                'taux'      => $taux,
            ],
        ]);
    }

    // ── PATCH /api/memoires/{id}/checklist/{item_id} ──────────────────────────
    // Cocher / décocher une étape (encadrant uniquement)
    public function toggle(Request $request, $id_memoire, $id_item)
    {
        $item = ChecklistItem::where('id_memoire', $id_memoire)
                             ->where('id_item', $id_item)
                             ->firstOrFail();

        $request->validate([
            'est_complete' => 'required|boolean',
            'commentaire'  => 'nullable|string|max:500',
        ]);

        $item->est_complete    = $request->est_complete;
        $item->date_completion = $request->est_complete ? now() : null;
        $item->complete_par    = $request->est_complete ? Auth::id() : null;
        $item->commentaire     = $request->commentaire ?? $item->commentaire;
        $item->save();

        // Recalculer le taux
        $total     = ChecklistItem::where('id_memoire', $id_memoire)->count();
        $completes = ChecklistItem::where('id_memoire', $id_memoire)->where('est_complete', true)->count();
        $taux      = $total > 0 ? round(($completes / $total) * 100) : 0;

        return response()->json([
            'item'        => $item,
            'progression' => [
                'total'     => $total,
                'completes' => $completes,
                'taux'      => $taux,
            ],
            'message' => $request->est_complete
                ? 'Étape marquée comme complète.'
                : 'Étape réouverte.',
        ]);
    }

    // ── POST /api/memoires/{id}/checklist ─────────────────────────────────────
    // Ajouter une étape personnalisée
    public function store(Request $request, $id_memoire)
    {
        Memoire::findOrFail($id_memoire);

        $request->validate([
            'libelle'    => 'required|string|max:200',
            'categorie'  => 'nullable|string|max:50',
            'commentaire'=> 'nullable|string|max:500',
        ]);

        $dernierOrdre = ChecklistItem::where('id_memoire', $id_memoire)->max('ordre') ?? 0;

        $item = ChecklistItem::create([
            'id_memoire'  => $id_memoire,
            'libelle'     => $request->libelle,
            'categorie'   => $request->categorie ?? 'general',
            'ordre'       => $dernierOrdre + 1,
            'commentaire' => $request->commentaire,
        ]);

        return response()->json([
            'item'    => $item,
            'message' => 'Étape ajoutée avec succès.',
        ], 201);
    }

    // ── DELETE /api/memoires/{id}/checklist/{item_id} ─────────────────────────
    // Supprimer une étape personnalisée
    public function destroy($id_memoire, $id_item)
    {
        $item = ChecklistItem::where('id_memoire', $id_memoire)
                             ->where('id_item', $id_item)
                             ->firstOrFail();
        $item->delete();

        return response()->json(['message' => 'Étape supprimée.']);
    }

    // ── Méthode privée : création des étapes par défaut ───────────────────────
    private function creerEtapesParDefaut(int $id_memoire): void
    {
        foreach ($this->etapesParDefaut as $etape) {
            ChecklistItem::create(array_merge($etape, ['id_memoire' => $id_memoire]));
        }
    }
}
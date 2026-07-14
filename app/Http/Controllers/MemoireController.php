<?php

namespace App\Http\Controllers;

use App\Models\Memoire;
use App\Models\Encadreur;
use App\Models\Notification;
use App\Models\AdminEcole;
use App\Models\Filiere;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemoireController extends Controller
{
    public function index(Request $request)
    {
        $user  = Auth::user();
        $query = Memoire::with(['filiere', 'encadreurs.etudiant.utilisateur', 'encadreurs.encadrant.utilisateur', 'versions']);

        if ($user->role === 'etudiant') {
            $query->whereHas('etudiants', fn($q) => $q->where('etudiants.id_user', $user->id_user));
        } elseif ($user->role === 'encadrant') {
            $query->whereHas('encadrants', fn($q) => $q->where('encadrants.id_user', $user->id_user));
        } elseif ($user->role === 'admin_ecole') {
            $adminEcole = AdminEcole::where('id_user', $user->id_user)->first();
            if ($adminEcole) {
                $query->whereHas('filiere', fn($q) => $q->where('id_etablissement', $adminEcole->id_etablissement));
            }
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'titre'        => 'required|string|max:255',
            'id_filiere'   => 'required|exists:filieres,id_filiere',
            'id_etudiant'  => 'required|exists:etudiants,id_user',
            'id_encadrant' => 'required|exists:encadrants,id_user',
        ]);

        $user = Auth::user();

        if ($user->role !== 'admin_ecole') {
            return response()->json(['message' => 'Seul un Admin Ecole peut créer un mémoire.'], 403);
        }

        // Vérifier que la filière appartient à l'établissement de l'admin
        $adminEcole = AdminEcole::where('id_user', $user->id_user)->first();
        $filiere = Filiere::findOrFail($request->id_filiere);
        if ($filiere->id_etablissement !== $adminEcole->id_etablissement) {
            return response()->json(['message' => 'Cette filière n\'appartient pas à votre établissement.'], 403);
        }

        // Vérifier qu'un étudiant n'a pas déjà un mémoire
        $dejaMemoire = Encadreur::where('id_etudiant', $request->id_etudiant)->exists();
        if ($dejaMemoire) {
            return response()->json(['message' => 'Cet étudiant a déjà un mémoire assigné.'], 422);
        }
$memoire = Memoire::create([
    'titre'      => $request->titre,
    'statut'     => 'cree',
    'id_filiere' => $request->id_filiere,
]);

        Encadreur::create([
            'id_memoire'   => $memoire->id_memoire,
            'id_etudiant'  => $request->id_etudiant,
            'id_encadrant' => $request->id_encadrant,
        ]);

        return response()->json([
            'message' => 'Mémoire créé avec succès.',
            'memoire' => $memoire->load(['encadreurs.etudiant.utilisateur', 'encadreurs.encadrant.utilisateur', 'filiere'])
        ], 201);
    }

    public function show($id)
    {
        $user    = Auth::user();
        $memoire = Memoire::with([
            'filiere',
            'encadreurs.etudiant.utilisateur',
            'encadreurs.encadrant.utilisateur',
            'versions.annotations'
        ])->findOrFail($id);

        if ($user->role === 'etudiant') {
            $estEtudiant = $memoire->etudiants->contains('id_user', $user->id_user);
            if (!$estEtudiant) {
                return response()->json(['message' => 'Accès refusé.'], 403);
            }
        }

        if ($user->role === 'encadrant') {
            $estEncadrant = $memoire->encadrants->contains('id_user', $user->id_user);
            if (!$estEncadrant) {
                return response()->json(['message' => 'Accès refusé.'], 403);
            }
        }

        return response()->json($memoire);
    }

    public function update(Request $request, $id)
    {
        $user    = Auth::user();
        $memoire = Memoire::findOrFail($id);

        if (in_array($user->role, ['admin_ecole', 'admin_plateforme'])) {
            $data = $request->validate([
                'titre'        => 'sometimes|required|string|max:255',
                'id_filiere'   => 'sometimes|required|exists:filieres,id_filiere',
                'id_etudiant'  => 'sometimes|required|exists:etudiants,id_user',
                'id_encadrant' => 'sometimes|required|exists:encadrants,id_user',
            ]);

            if (isset($data['titre'])) {
                $memoire->update(['titre' => $data['titre']]);
            }
            if (isset($data['id_filiere'])) {
                $memoire->update(['id_filiere' => $data['id_filiere']]);
            }
            if (isset($data['id_etudiant']) || isset($data['id_encadrant'])) {
                $encadreur = Encadreur::where('id_memoire', $id)->first();
                if ($encadreur) {
                    if (isset($data['id_etudiant'])) {
                        $encadreur->update(['id_etudiant' => $data['id_etudiant']]);
                    }
                    if (isset($data['id_encadrant'])) {
                        $encadreur->update(['id_encadrant' => $data['id_encadrant']]);
                    }
                }
            }
        } elseif ($user->role === 'etudiant') {
            $estEtudiant = $memoire->etudiants->contains('id_user', $user->id_user);
            if (!$estEtudiant) {
                return response()->json(['message' => 'Accès refusé.'], 403);
            }
            if (!in_array($memoire->statut, ['en_cours', 'rejete'])) {
                return response()->json(['message' => 'Modification impossible dans ce statut.'], 422);
            }
            $data = $request->validate(['titre' => 'sometimes|required|string|max:255']);
            $memoire->update($data);
        }

        return response()->json($memoire->load(['filiere', 'encadreurs.etudiant.utilisateur', 'encadreurs.encadrant.utilisateur']));
    }

    public function changerStatut(Request $request, $id)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['encadrant', 'admin_ecole', 'admin_plateforme'])) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $memoire = Memoire::findOrFail($id);

        if ($user->role === 'encadrant') {
            $estEncadrant = $memoire->encadrants->contains('id_user', $user->id_user);
            if (!$estEncadrant) {
                return response()->json(['message' => 'Vous n\'encadrez pas ce mémoire.'], 403);
            }
        }

        $data = $request->validate([
            'statut' => 'required|in:cree,en_cours,soutenu',
        ]);

        if ($data['statut'] === 'soutenu' && !in_array($user->role, ['admin_ecole', 'admin_plateforme'])) {
            return response()->json(['message' => 'Seul un Admin Ecole peut clôturer un mémoire en soutenu. (RG25)'], 403);
        }

        $memoire->update(['statut' => $data['statut']]);

$typeMap = [
    'Validé' => 'Validé',
];

        if (isset($typeMap[$data['statut']])) {
            foreach ($memoire->etudiants as $etudiant) {
                Notification::create([
                    'type_evenement' => $typeMap[$data['statut']],
                    'message'        => "Votre mémoire \"{$memoire->titre}\" a été {$data['statut']}.",
                    'id_user'        => $etudiant->id_user,
                    'id_version'     => null,
                ]);
            }
        }

        return response()->json($memoire);
    }

    public function assignerEncadrant(Request $request, $id)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['admin_ecole', 'admin_plateforme'])) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $data = $request->validate([
            'id_encadrant' => 'required|exists:encadrants,id_user',
            'id_etudiant'  => 'required|exists:etudiants,id_user',
        ]);

        $encadreur = Encadreur::where('id_memoire', $id)
                        ->where('id_etudiant', $data['id_etudiant'])
                        ->firstOrFail();

        $encadreur->update(['id_encadrant' => $data['id_encadrant']]);

        return response()->json([
            'message'   => 'Encadrant réassigné avec succès.',
            'encadreur' => $encadreur
        ]);
    }

    public function destroy($id)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['admin_ecole', 'admin_plateforme'])) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $memoire = Memoire::findOrFail($id);
        Encadreur::where('id_memoire', $id)->delete();
        $memoire->delete();

        return response()->json(['message' => 'Mémoire supprimé.']);
    }
}
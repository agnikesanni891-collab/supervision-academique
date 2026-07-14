<?php

namespace App\Http\Controllers;

use App\Models\Annotation;
use App\Models\Notification;
use App\Models\VersionMemoire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnotationController extends Controller
{
    public function index($id_version)
    {
        $user    = Auth::user();
        $version = VersionMemoire::with('memoire')->findOrFail($id_version);
        $memoire = $version->memoire;

        if ($user->role === 'etudiant' && !$memoire->etudiants->contains('id_user', $user->id_user)) {
            return response()->json(['message'=> 'Accès refusé.'], 403);
        }
        if ($user->role === 'encadrant' && !$memoire->encadrants->contains('id_user', $user->id_user)) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        return response()->json(
            Annotation::where('id_version', $id_version)
                ->orderBy('id_annotation', 'asc')
                ->get()
        );
    }

    public function store(Request $request, $id_version)
    {
        $user    = Auth::user();
        $version = VersionMemoire::with('memoire')->findOrFail($id_version);
        $memoire = $version->memoire;

        if ($user->role !== 'encadrant' || !$memoire->encadrants->contains('id_user', $user->id_user)) {
            return response()->json(['message' => 'Seul l\'encadrant assigné peut annoter ce mémoire.'], 403);
        }

        $data = $request->validate([
            'page'              => 'required|integer|min:1',
            'position_x'       => 'nullable|numeric',
            'position_y'       => 'nullable|numeric',
            'largeur'          => 'nullable|numeric',
            'hauteur'          => 'nullable|numeric',
            'texte_surligne'   => 'nullable|string',
            'texte_commentaire'=> 'required|string',
            'rects'            => 'nullable|string',
        ]);

        $annotation = Annotation::create([
            'page'              => $data['page'],
            'position_x'       => $data['position_x']    ?? 0,
            'position_y'       => $data['position_y']    ?? 0,
            'largeur'          => $data['largeur']        ?? 0,
            'hauteur'          => $data['hauteur']        ?? 0,
            'texte_surligne'   => $data['texte_surligne'] ?? null,
            'texte_commentaire'=> $data['texte_commentaire'],
            'rects'            => $data['rects']          ?? null,
            'id_version'       => $id_version,
            'id_encadrant'     => $user->id_user,
        ]);

        foreach ($memoire->etudiants as $etudiant) {
            Notification::create([
                'type_evenement' => 'annotation',
                'message'        => "Votre encadrant a ajouté un commentaire sur la version {$version->numero_version} de votre mémoire.",
                'id_user'        => $etudiant->id_user,
                'id_version'     => $id_version,
            ]);
        }

        return response()->json($annotation, 201);
    }

    public function update(Request $request, $id)
    {
        $user       = Auth::user();
        $annotation = Annotation::findOrFail($id);

        if ($annotation->id_encadrant !== $user->id_user) {
            return response()->json(['message' => 'Vous ne pouvez modifier que vos propres annotations.'], 403);
        }

        $data = $request->validate([
            'texte_commentaire' => 'required|string',
            'page'              => 'sometimes|integer|min:1',
            'position_x'       => 'sometimes|numeric',
            'position_y'       => 'sometimes|numeric',
            'largeur'          => 'nullable|numeric',
            'hauteur'          => 'nullable|numeric',
            'texte_surligne'   => 'nullable|string',
            'rects'            => 'nullable|string',
        ]);

        $annotation->update($data);
        return response()->json($annotation);
    }

    public function destroy($id)
    {
        $user       = Auth::user();
        $annotation = Annotation::findOrFail($id);

        if ($annotation->id_encadrant !== $user->id_user && !in_array($user->role, ['admin_ecole', 'admin_plateforme'])) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $annotation->delete();
        return response()->json(['message' => 'Annotation supprimée.']);
    }
}
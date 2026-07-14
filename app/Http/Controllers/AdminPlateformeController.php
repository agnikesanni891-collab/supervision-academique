<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Utilisateur;
use App\Models\AdminPlateforme;

class AdminPlateformeController extends Controller
{
    // Lister tous les admins plateforme
    public function index()
    {
        $admins = AdminPlateforme::with('utilisateur')->get();
        return response()->json($admins);
    }

    // Voir un admin plateforme
    public function show($id)
    {
        $admin = AdminPlateforme::with('utilisateur')->findOrFail($id);
        return response()->json($admin);
    }

    // Supprimer un admin plateforme
// Désactiver
public function desactiver($id)
{
    $user = Utilisateur::findOrFail($id);
    $user->delete(); // soft delete
    return response()->json(['message' => 'Admin Plateforme désactivé.']);
}

// Supprimer définitivement
public function destroy($id)
{
    $user = Utilisateur::findOrFail($id);
    $user->forceDelete();
    return response()->json(['message' => 'Admin Plateforme supprimé définitivement.']);
}}

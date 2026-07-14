<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Utilisateur;
use App\Models\AdminEcole;

class AdminEcoleController extends Controller
{
    public function index()
    {
        $admins = AdminEcole::with(['utilisateur', 'etablissement'])->get();
        return response()->json($admins);
    }

    public function show($id)
    {
        $admin = AdminEcole::with(['utilisateur', 'etablissement'])->findOrFail($id);
        return response()->json($admin);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom'              => 'required|string|max:100',
            'prenom'           => 'required|string|max:100',
            'email'            => 'required|email|unique:utilisateurs,email',
            'telephone'        => 'nullable|string|max:20',
            'mot_de_passe'     => 'required|string|min:6',
            'id_etablissement' => 'required|exists:etablissements,id_etablissement',
        ]);

        // Créer l'utilisateur
        $utilisateur = Utilisateur::create([
            'nom'          => $request->nom,
            'prenom'       => $request->prenom,
            'email'        => $request->email,
            'telephone'    => $request->telephone,
            'mot_de_passe' => Hash::make($request->mot_de_passe),
            'role'         => 'admin_ecole',
            'actif'        => true,
        ]);

        // Créer l'entrée adminecole
        AdminEcole::create([
            'id_user'          => $utilisateur->id_user,
            'id_etablissement' => $request->id_etablissement,
        ]);

        return response()->json(
            AdminEcole::with(['utilisateur', 'etablissement'])
                ->where('id_user', $utilisateur->id_user)
                ->first(),
            201
        );
    }

    public function update(Request $request, $id)
    {
        $admin = AdminEcole::findOrFail($id);
        $utilisateur = Utilisateur::findOrFail($admin->id_user);

        $request->validate([
            'nom'              => 'sometimes|string|max:100',
            'prenom'           => 'sometimes|string|max:100',
            'email'            => 'sometimes|email|unique:utilisateurs,email,' . $utilisateur->id_user . ',id_user',
            'telephone'        => 'nullable|string|max:20',
            'mot_de_passe'     => 'nullable|string|min:6',
            'id_etablissement' => 'sometimes|exists:etablissements,id_etablissement',
        ]);

        $utilisateur->update([
            'nom'      => $request->nom      ?? $utilisateur->nom,
            'prenom'   => $request->prenom   ?? $utilisateur->prenom,
            'email'    => $request->email    ?? $utilisateur->email,
            'telephone'=> $request->telephone ?? $utilisateur->telephone,
            ...($request->mot_de_passe ? ['mot_de_passe' => Hash::make($request->mot_de_passe)] : []),
        ]);

        if ($request->id_etablissement) {
            $admin->update(['id_etablissement' => $request->id_etablissement]);
        }

        return response()->json(
            AdminEcole::with(['utilisateur', 'etablissement'])
                ->findOrFail($id)
        );
    }

public function toggleActif($id)
{
    $admin = AdminEcole::findOrFail($id);
    $utilisateur = Utilisateur::where('id_user', $admin->id_user)->firstOrFail();
    $utilisateur->update(['est_actif' => !$utilisateur->est_actif]);

    return response()->json([
        'message' => $utilisateur->est_actif ? 'Compte activé.' : 'Compte désactivé.',
        'actif'   => $utilisateur->est_actif,
    ]);
}

   public function destroy($id)
{
    $admin = AdminEcole::findOrFail($id);
    Utilisateur::where('id_user', $admin->id_user)->delete();
    $admin->delete();
    return response()->json(['message' => 'Admin École supprimé.']);
}
}
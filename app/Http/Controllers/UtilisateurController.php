<?php

namespace App\Http\Controllers;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\Utilisateur;
use App\Models\Etudiant;
use App\Models\Encadrant;
use App\Models\AdminEcole;
use App\Models\AdminPlateforme;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UtilisateurController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin_ecole') {
            $users = Utilisateur::with(['etudiant.filiere', 'encadrant', 'adminEcole'])
                ->where('created_by', $user->id_user)
                ->get();
            return response()->json($users);
        }

        $users = Utilisateur::with([
            'etudiant.filiere',
            'encadrant',
            'adminEcole'
        ])->get();

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom'              => 'required|string|max:50',
            'prenom'           => 'required|string|max:50',
            'email'            => 'required|email|unique:utilisateurs,email',
            'mot_de_passe'     => 'required|string|min:6',
            'role'             => 'required|in:etudiant,encadrant,admin_ecole,admin_plateforme',
            'id_filiere'       => 'required_if:role,etudiant|exists:filieres,id_filiere',
            'specialite'       => 'nullable|string|max:100',
            'grade'            => 'nullable|string|max:50',
            'id_etablissement' => 'required_if:role,admin_ecole|exists:etablissements,id_etablissement',
        ]);

        $user = Utilisateur::create([
            'nom'          => $request->nom,
            'prenom'       => $request->prenom,
            'email'        => $request->email,
            'mot_de_passe' => Hash::make($request->mot_de_passe),
            'role'         => $request->role,
            'est_actif'    => true,
            'created_by'   => Auth::user()->id_user,
        ]);

        switch ($request->role) {
            case 'etudiant':
                Etudiant::create([
                    'id_user'    => $user->id_user,
                    'matricule'  => uniqid('MAT'),
                    'id_filiere' => $request->id_filiere,
                ]);
                return response()->json([
                    'message'     => 'Compte créé avec succès.',
                    'utilisateur' => $user->load('etudiant.filiere')
                ], 201);

            case 'encadrant':
                Encadrant::create([
                    'id_user'    => $user->id_user,
                    'specialite' => $request->specialite,
                    'grade'      => $request->grade,
                ]);
                return response()->json([
                    'message'     => 'Compte créé avec succès.',
                    'utilisateur' => $user->load('encadrant')
                ], 201);

            case 'admin_ecole':
                AdminEcole::create([
                    'id_user'          => $user->id_user,
                    'id_etablissement' => $request->id_etablissement,
                ]);
                return response()->json([
                    'message'     => 'Compte créé avec succès.',
                    'utilisateur' => $user->load('adminEcole.etablissement')
                ], 201);

            case 'admin_plateforme':
                AdminPlateforme::create([
                    'id_user' => $user->id_user,
                ]);
                return response()->json([
                    'message'     => 'Compte créé avec succès.',
                    'utilisateur' => $user->load('adminPlateforme')
                ], 201);
        }
    }

    public function show($id)
    {
        $user = Utilisateur::with([
            'etudiant.filiere',
            'encadrant',
            'adminEcole.etablissement'
        ])->findOrFail($id);

        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $user = Utilisateur::findOrFail($id);

        $request->validate([
            'nom'          => 'sometimes|required|string|max:50',
            'prenom'       => 'sometimes|required|string|max:50',
            'email'        => 'sometimes|required|email|unique:utilisateurs,email,' . $id . ',id_user',
            'telephone'    => 'nullable|string|max:20',
            'mot_de_passe' => 'sometimes|nullable|string|min:6',
        ]);

        $data = $request->only(['nom', 'prenom', 'email', 'telephone']);

        if ($request->filled('mot_de_passe')) {
            $data['mot_de_passe'] = Hash::make($request->mot_de_passe);
        }

        $user->update($data);

        return response()->json([
            'message'     => 'Profil mis à jour avec succès.',
            'utilisateur' => $user
        ]);
    }

    public function toggleActif($id)
    {
        $user = Utilisateur::findOrFail($id);
        $user->update(['est_actif' => !$user->est_actif]);
        $statut = $user->est_actif ? 'activé' : 'désactivé';

        return response()->json([
            'message' => 'Compte ' . $statut . ' avec succès.',
            'user'    => $user
        ]);
    }
public function destroy($id)
{
    $user = Utilisateur::findOrFail($id);

    // Supprimer les enregistrements liés
    Etudiant::where('id_user', $id)->delete();
    Encadrant::where('id_user', $id)->delete();
    AdminEcole::where('id_user', $id)->delete();
    AdminPlateforme::where('id_user', $id)->delete();
    Notification::where('id_user', $id)->delete();

    $user->delete();

    return response()->json(['message' => 'Compte supprimé avec succès.']);
}
}
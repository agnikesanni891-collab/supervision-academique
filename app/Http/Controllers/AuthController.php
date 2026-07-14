<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // LOGIN
    public function login(Request $request)
    {
        $request->validate([
            'email'       => 'required|email',
            'mot_de_passe' => 'required|string',
        ]);

        $utilisateur = Utilisateur::where('email', $request->email)->first();

        // Vérification email + mot de passe + compte actif
        if (!$utilisateur || !Hash::check($request->mot_de_passe, $utilisateur->mot_de_passe)) {
            return response()->json([
                'message' => 'Email ou mot de passe incorrect.'
            ], 401);
        }

        if (!$utilisateur->est_actif) {
            return response()->json([
                'message' => 'Votre compte est désactivé.'
            ], 403);
        }

        // Supprime les anciens tokens et crée un nouveau
        $utilisateur->tokens()->delete();
        $token = $utilisateur->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'      => 'Connexion réussie.',
            'token'        => $token,
            'utilisateur'  => [
                'id_user' => $utilisateur->id_user,
                'nom'     => $utilisateur->nom,
                'prenom'  => $utilisateur->prenom,
                'email'   => $utilisateur->email,
                'role'    => $utilisateur->role,
            ]
        ], 200);
    }

    // LOGOUT
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie.'
        ], 200);
    }

    // PROFIL CONNECTÉ
    public function me(Request $request)
    {
        return response()->json($request->user(), 200);
    }
}
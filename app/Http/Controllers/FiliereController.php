<?php

namespace App\Http\Controllers;

use App\Models\Filiere;
use App\Models\AdminEcole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FiliereController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin_ecole') {
            $adminEcole = AdminEcole::where('id_user', $user->id_user)->first();
            if ($adminEcole) {
                $filieres = Filiere::where('id_etablissement', $adminEcole->id_etablissement)->get();
                return response()->json($filieres);
            }
        }

        $filieres = Filiere::all();
        return response()->json($filieres);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'libelle_filiere'  => 'required|string|max:255',
            'id_etablissement' => 'sometimes|required|integer',
        ]);

        if ($user->role === 'admin_ecole') {
            $adminEcole = AdminEcole::where('id_user', $user->id_user)->first();
            if ($adminEcole) {
                $data['id_etablissement'] = $adminEcole->id_etablissement;
            }
        }

        $filiere = Filiere::create($data);
        return response()->json($filiere, 201);
    }

    public function show($id)
    {
        $filiere = Filiere::findOrFail($id);
        return response()->json($filiere);
    }

    public function update(Request $request, $id)
    {
        $filiere = Filiere::findOrFail($id);
        $filiere->update($request->all());
        return response()->json($filiere);
    }

    public function destroy($id)
    {
        Filiere::findOrFail($id)->delete();
        return response()->json(['message' => 'Filière supprimée.']);
    }
}
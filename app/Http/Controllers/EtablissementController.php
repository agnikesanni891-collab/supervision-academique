<?php

namespace App\Http\Controllers;

use App\Models\Etablissement;
use Illuminate\Http\Request;

class EtablissementController extends Controller
{
    public function index()
    {
        $etablissements = Etablissement::with('filieres')->get();
        return response()->json($etablissements);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom_etablissement' => 'required|string|max:255',
            'ville'             => 'nullable|string|max:100',
            'adresse'           => 'nullable|string|max:255',
            'telephone'         => 'nullable|string|max:20',
        ]);

        $etab = Etablissement::create($data);
        return response()->json($etab, 201);
    }

    public function show($id)
    {
        $etab = Etablissement::with('filieres')->findOrFail($id);
        return response()->json($etab);
    }

    public function update(Request $request, $id)
    {
        $etab = Etablissement::findOrFail($id);

        $data = $request->validate([
            'nom_etablissement' => 'sometimes|required|string|max:255',
            'ville'             => 'nullable|string|max:100',
            'adresse'           => 'nullable|string|max:255',
            'telephone'         => 'nullable|string|max:20',
        ]);

        $etab->update($data);
        return response()->json($etab);
    }

    public function destroy($id)
    {
        $etab = Etablissement::findOrFail($id);
        $etab->delete();
        return response()->json(['message' => 'Établissement supprimé.']);
    }
}
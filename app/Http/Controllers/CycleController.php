<?php

namespace App\Http\Controllers;

use App\Models\Cycle;
use Illuminate\Http\Request;

class CycleController extends Controller
{
    public function index()
    {
        return response()->json(Cycle::orderBy('libelle')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'libelle' => 'required|string|max:100|unique:cycles,libelle'
        ]);

        $cycle = Cycle::create(['libelle' => $request->libelle]);

        return response()->json($cycle, 201);
    }

    public function show($id)
    {
        return response()->json(Cycle::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $cycle = Cycle::findOrFail($id);
        $request->validate([
            'libelle' => 'required|string|max:100|unique:cycles,libelle,' . $id . ',id_cycle'
        ]);
        $cycle->update(['libelle' => $request->libelle]);
        return response()->json($cycle);
    }

    public function destroy($id)
    {
        Cycle::findOrFail($id)->delete();
        return response()->json(['message' => 'Cycle supprimé.']);
    }
}
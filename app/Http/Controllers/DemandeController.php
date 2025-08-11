<?php

namespace App\Http\Controllers;
use App\Models\demande;
use Illuminate\Http\Request;

class DemandeController extends Controller
{
 public function index(Request $request)
{
    try {
        $statuts = demande::paginate(5); // 5 demandes par page
        return response()->json($statuts);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Problème de récupération de la liste des demandes',
            'error' => $e->getMessage()
        ], 500);
    }
}
public function store(Request $request)
{
    try {
        $demande = new Demande([
            'raison' => $request->input('raison'),
            'dateDemande' => $request->input('dateDemande'),
            'dateSortie' => $request->input('dateSortie'),
            'dateRetour' => $request->input('dateRetour'),
    'statutId' => $request->input('statutId', 1), // valeur par défaut = 1
            'emailEmployee' => $request->input('emailEmployee'),
            'dateSortiePause' => $request->input('dateSortiePause'),
        ]);

        $demande->save();

        return response()->json($demande, 201);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Insertion impossible',
            'error' => $e->getMessage(),
        ], 500);
    }
}

}

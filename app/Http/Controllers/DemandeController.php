<?php

namespace App\Http\Controllers;
use App\Models\demande;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
            'statutId' => $request->input('statutId', 1),
            'emailEmployee' => $request->input('emailEmployee'),
            'dateSortiePause' => $request->input('dateSortiePause'),
        ]);

        // Conversion heures
        $sortie = Carbon::createFromFormat('H:i', $request->dateSortie);
        $retour = Carbon::createFromFormat('H:i', $request->dateRetour);
        $dureeMinutes = $sortie->diffInMinutes($retour);

        // 🔹 Durée négative
        if ($dureeMinutes <= 0) {
            return response()->json([
                'message' => 'La date de retour doit être supérieure à la date de sortie'
            ], 422);
        }

        // 🔹 Plafond journalier 2h
        $totalMinutesJour = Demande::where('emailEmployee', $request->emailEmployee)
            ->where('dateSortiePause', $request->dateSortiePause)
            ->get()
            ->sum(fn($dem) => Carbon::parse($dem->dateSortie)->diffInMinutes(Carbon::parse($dem->dateRetour)));

        $totalMinutesJour += $dureeMinutes;
        if ($totalMinutesJour > 120) {
            return response()->json([
                'message' => 'La somme des pauses pour ce jour dépasse 2 heures.'
            ], 422);
        }

        // 🔹 Plafond mensuel 8h
        $debutMois = Carbon::parse($request->dateSortiePause)->startOfMonth();
        $finMois = Carbon::parse($request->dateSortiePause)->endOfMonth();

        $totalMinutesMois = Demande::where('emailEmployee', $request->emailEmployee)
            ->whereBetween('dateSortiePause', [$debutMois, $finMois])
            ->get()
            ->sum(fn($dem) => Carbon::parse($dem->dateSortie)->diffInMinutes(Carbon::parse($dem->dateRetour)));

        $totalMinutesMois += $dureeMinutes;
        if ($totalMinutesMois > 480) {
            return response()->json([
                'message' => 'La somme des pauses pour ce mois dépasse 8 heures.'
            ], 422);
        }

        // 🔹 Tout OK → sauvegarde
        $demande->save();

        return response()->json($demande, 201);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Insertion impossible',
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function destroy( $id) {
    {
            try { 
$statut=Demande::findOrFail($id); 
$statut->delete(); 
return response()->json("statut supprimée avec succes"); 
} catch (\Exception $e) { 
return response()->json("probleme de suppression de Staut"); 
} 
} 
    }

//        public function update(Request $request, $id) {
//     {
//         try { 
// $statut=Demande::findorFail($id); 
// $statut->update($request->all()); 
// return response()->json($statut); 
// } catch (\Exception $e) { 
// return response()->json("probleme de modification"); 
// } 
//     }
//     }
 public function updateStatut(Request $request, $id)
    {
        // Validation
        $request->validate([
            'statutId' => 'required|integer|exists:statuts,id',
        ]);

        // Récupérer la demande
        $demande = Demande::find($id);

        if (!$demande) {
            return response()->json([
                'message' => 'Demande non trouvée.'
            ], 404);
        }

        // Mettre à jour le statut
        $demande->statutId = $request->statutId;
        $demande->save();

        return response()->json([
            'message' => 'Statut mis à jour avec succès.',
            'demande' => $demande
        ]);
    }
}
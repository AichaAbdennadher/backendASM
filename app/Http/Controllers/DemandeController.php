<?php

namespace App\Http\Controllers;
use App\Models\demande;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\DemandeBreakMail;
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
// ✅ Envoi d’un email à l’admin
        Mail::to("abdennadheraicha19@gmail.com")->send(new DemandeBreakMail($demande));
        return response()->json($demande, 201);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Insertion impossible',
            'error' => $e->getMessage()
        ], 500);
    }
}
public function getPendingDemande()
{
    try {
        $demandes = Demande::where('statutId', 1)->get(); // 1 = en attente
        return response()->json($demandes);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Impossible de récupérer les demandes',
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
    public function countDemandes()
    {
        try {
            // Récupérer le nombre total de demandes
            $count = Demande::count();

            return response()->json([
                'success' => true,
                'count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du comptage des demandes',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function countDemandesAccept()
{
    try {
        // Récupérer le nombre de demandes avec statutId = 2
        $count = Demande::where('statutId', 2)->count();

        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors du comptage des demandes',
            'error' => $e->getMessage()
        ], 500);
    }
}
    public function countDemandesRefuse()
{
    try {
        $count = Demande::where('statutId', 3)->count();

        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors du comptage des demandes',
            'error' => $e->getMessage()
        ], 500);
    }
}
public function countDemandesPerMonth()
{
    try {
        // Récupérer les demandes groupées par mois pour l'année en cours
        $year = Carbon::now()->year;

        $counts = Demande::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as total')
            )
            ->whereYear('created_at', $year)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy('month')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $counts
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors du comptage des demandes par mois',
            'error' => $e->getMessage()
        ], 500);
    }
}
public function demandesParSemaineMois()
{
    $startOfMonth = Carbon::now()->startOfMonth();
    $endOfMonth   = Carbon::now()->endOfMonth();

    $demandes = DB::table('demandes')
        ->selectRaw('FLOOR((DAY(created_at) - 1) / 7) + 1 as week_in_month, COUNT(*) as total')
        ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
        ->groupBy('week_in_month')
        ->orderBy('week_in_month')
        ->get();

    return response()->json([
        'success' => true,
        'data' => $demandes
    ]);
}

public function countAcceptedDemandesPerMonth()
{
    try {
        $year = Carbon::now()->year;

        $counts = Demande::select(
                DB::raw('MONTH(dateDemande) as month'),
                DB::raw('COUNT(*) as total')
            )
            ->whereYear('dateDemande', $year)
            ->where('statutId', 2) // 2 = accepté
            ->groupBy(DB::raw('MONTH(dateDemande)'))
            ->orderBy('month')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $counts
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors du comptage des demandes acceptées par mois',
            'error' => $e->getMessage()
        ], 500);
    }
}
public function countRefusedDemandesPerMonth()
{
    try {
        $year = Carbon::now()->year;

        $counts = Demande::select(
                DB::raw('MONTH(dateDemande) as month'),
                DB::raw('COUNT(*) as total')
            )
            ->whereYear('dateDemande', $year)
            ->where('statutId', 3) // 3 = refusé
            ->groupBy(DB::raw('MONTH(dateDemande)'))
            ->orderBy('month')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $counts
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors du comptage des demandes refusées par mois',
            'error' => $e->getMessage()
        ], 500);
    }
}
public function getLastDemandes()
{
    try {
        $demandes = Demande::orderBy('created_at', 'desc') // tri du plus récent au plus ancien
                          ->take(7) // prendre uniquement 5
                          ->get();

        return response()->json([
            'success' => true,
            'data' => $demandes
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la récupération des demandes',
            'error' => $e->getMessage()
        ], 500);
    }
}

///dashboard employée
public function countDemandesForUser()
{
    try {
        $email = auth()->user()->email; // 
        $count = Demande::where('emailEmployee', $email)->count();

        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors du comptage des demandes',
            'error' => $e->getMessage()
        ], 500);
    }
}


public function countDemandesAcceptForUser()
{
    try {
        $email = auth()->user()->email;
        $count = Demande::where('emailEmployee', $email)->where('statutId', 2)->count();

        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors du comptage des demandes acceptées',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function countDemandesRefuseForUser()
{
    try {
        $email = auth()->user()->email;
        $count = Demande::where('emailEmployee', $email)->where('statutId', 3)->count();

        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors du comptage des demandes refusées',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function countDemandesPerMonthForUser()
{
    try {
        $email = auth()->user()->email;
        $year = Carbon::now()->year;

        $counts = Demande::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as total')
            )
            ->where('emailEmployee', $email)
            ->whereYear('created_at', $year)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy('month')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $counts
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors du comptage des demandes par mois',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function demandesParSemaineMoisForUser()
{
    $email = auth()->user()->email;
    $startOfMonth = Carbon::now()->startOfMonth();
    $endOfMonth   = Carbon::now()->endOfMonth();

    $demandes = DB::table('demandes')
        ->selectRaw('FLOOR((DAY(created_at) - 1) / 7) + 1 as week_in_month, COUNT(*) as total')
        ->where('emailEmployee', $email)
        ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
        ->groupBy('week_in_month')
        ->orderBy('week_in_month')
        ->get();

    return response()->json([
        'success' => true,
        'data' => $demandes
    ]);
}

public function countAcceptedDemandesPerMonthForUser()
{
    try {
        $email = auth()->user()->email;
        $year = Carbon::now()->year;

        $counts = Demande::select(
                DB::raw('MONTH(dateDemande) as month'),
                DB::raw('COUNT(*) as total')
            )
            ->where('emailEmployee', $email)
            ->whereYear('dateDemande', $year)
            ->where('statutId', 2)
            ->groupBy(DB::raw('MONTH(dateDemande)'))
            ->orderBy('month')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $counts
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors du comptage des demandes acceptées par mois',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function countRefusedDemandesPerMonthForUser()
{
    try {
        $email = auth()->user()->email;
        $year = Carbon::now()->year;

        $counts = Demande::select(
                DB::raw('MONTH(dateDemande) as month'),
                DB::raw('COUNT(*) as total')
            )
            ->where('emailEmployee', $email)
            ->whereYear('dateDemande', $year)
            ->where('statutId', 3)
            ->groupBy(DB::raw('MONTH(dateDemande)'))
            ->orderBy('month')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $counts
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors du comptage des demandes refusées par mois',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function getLastDemandesForUser()
{
    try {
        $email = auth()->user()->email;
        $demandes = Demande::where('emailEmployee', $email)
                          ->orderBy('created_at', 'desc')
                          ->take(5)
                          ->get();

        return response()->json([
            'success' => true,
            'data' => $demandes
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la récupération des demandes',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function DemandesForUser(Request $request)
{
    try {
        // Récupérer l'utilisateur connecté
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }

        // Récupérer les demandes liées à son email
        $demandes = Demande::where('emailEmployee', $user->email)
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        return response()->json([
            'success' => true,
            'demandes' => $demandes
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Problème de récupération de la liste des demandes',
            'error' => $e->getMessage()
        ], 500);
    }
}

}
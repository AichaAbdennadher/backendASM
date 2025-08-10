<?php

namespace App\Http\Controllers;

use App\Models\Statut;
use Illuminate\Http\Request;

class StatutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    
 public function index(Request $request)
{
    try {
        $statuts = Statut::paginate(5); // 5 statuts par page
        return response()->json($statuts);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Problème de récupération de la liste des statuts',
            'error' => $e->getMessage()
        ], 500);
    }
}


    /**
     * Store a newly created resource in storage.
     */
    
    public function store(Request $request) //ajouter un  statut
    {
         try { 
$statut=new Statut([ 
"libelle"=>$request->input("libelle"), 
"couleur"=>$request->input("couleur") 
]); 
$statut->save();
return response()->json($statut); 
} catch (\Exception $e) { 
return response()->json("insertion impossible"); 
} 

    }

    /**
     * Display the specified resource.
     */
    public function show( $id) //affiche statut
    {
     try { 
$statut=Statut::findOrFail($id); 
return response()->json($statut); 
} catch (\Exception $e) { 
return response()->json("probleme de récupération des données"); } 

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {
    {
        try { 
$statut=Statut::findorFail($id); 
$statut->update($request->all()); 
return response()->json($statut); 
} catch (\Exception $e) { 
return response()->json("probleme de modification"); 
} 
    }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy( $id) {
    {
            try { 
$statut=Statut::findOrFail($id); 
$statut->delete(); 
return response()->json("statut supprimée avec succes"); 
} catch (\Exception $e) { 
return response()->json("probleme de suppression de Staut"); 
} 
} 
    }

}
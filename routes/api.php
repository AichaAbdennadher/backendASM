<?php

use App\Http\Controllers\DemandeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StatutController;



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware('api')->group(function () {
Route::resource('statuts', StatutController::class);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->get('/users', [AuthController::class, 'index']);
Route::get('/users/count', [AuthController::class, 'countUsers']);


// Demandes pour admin
Route::get('/demandes', [DemandeController::class, 'index']);
Route::post('/demandes', [DemandeController::class, 'store']);
Route::delete('/demandes/{id}', [DemandeController::class, 'destroy']);
Route::put('/demandes/{id}/statut', [DemandeController::class, 'updateStatut']);
Route::get('/demandes/count', [DemandeController::class, 'countDemandes']);
Route::get('/demandes/count/accepte', [DemandeController::class, 'countDemandesAccept']);
Route::get('/demandes/count/refuse', [DemandeController::class, 'countDemandesRefuse']);
Route::get('/demandes/count/month', [DemandeController::class, 'countDemandesPerMonth']);
Route::get('/demandes/count/week', [DemandeController::class, 'demandesParSemaineMois']);
Route::get('/demandes/count-per-month-accepted', [DemandeController::class, 'countAcceptedDemandesPerMonth']);
Route::get('/demandes/count-per-month-refused', [DemandeController::class, 'countRefusedDemandesPerMonth']);
Route::get('/demandes/last', [DemandeController::class, 'getLastDemandes']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/demandes/emp', [DemandeController::class, 'DemandesForUser']);
    Route::get('/employe/demandes/count', [DemandeController::class, 'countDemandesForUser']);
    Route::get('/employe/demandes/count/accepte', [DemandeController::class, 'countDemandesAcceptForUser']);
    Route::get('/employe/demandes/count/refuse', [DemandeController::class, 'countDemandesRefuseForUser']);
    Route::get('/employe/demandes/count/month', [DemandeController::class, 'countDemandesPerMonthForUser']);
    Route::get('/employe/demandes/count/week', [DemandeController::class, 'demandesParSemaineMoisForUser']);
    Route::get('/employe/demandes/count-per-month-accepted', [DemandeController::class, 'countAcceptedDemandesPerMonthForUser']);
    Route::get('/employe/demandes/count-per-month-refused', [DemandeController::class, 'countRefusedDemandesPerMonthForUser']);
    Route::get('/employe/demandes/last', [DemandeController::class, 'getLastDemandesForUser']);
});


<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
     public function index(Request $request)
{
    try {
        $users = User::where('role', 1)->paginate(5); // filtrer role=1
        return response()->json($users);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Problème de récupération de la liste des demandes',
            'error' => $e->getMessage()
        ], 500);
    }
}
     public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string',
            'familyName'     => 'required|string',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'job'      => 'required|string'
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'familyName'     => $validated['familyName'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'job'      => $validated['job'],
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ]);
    }

  public function login(Request $request)
{
    $request->validate([
        'email'    => 'required|email',
        'password' => 'required'
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json([
            'message' => 'Les informations d’identification sont incorrectes.'
        ], 401);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'user'  => $user,
        'token' => $token,
    ]);
}


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie',
        ]);
    }
   public function countUsers()
{
    try {
        $count = User::count() - 1; // exclure l'admin

        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors du comptage des utilisateurs',
            'error' => $e->getMessage()
        ], 500);
    }
}
}


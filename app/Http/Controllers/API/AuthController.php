<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB; 


class AuthController extends Controller
{
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->get();
        return response()->json(['users' => $users]);
    }
    
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));

        return response()->json(['message' => 'User successfully registered', 'user' => $user]);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (! $token = Auth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->createNewToken($token);
    }

    public function logout()
    {
        Auth::logout();

        return response()->json(['message' => 'User successfully logged out']);
    }

    protected function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60,
        ]);
    }

    public function update(Request $request, User $user)
    {
        // Récupère l'utilisateur authentifié
        $authenticatedUser = Auth::user();

        // Vérifie que l'utilisateur authentifié peut effectuer cette action
        if ($authenticatedUser->id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Sauvegarde temporaire de l'ancien chemin de l'image
        $file_temp = $user->image;

        // Validation des données reçues
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:6',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048', // Taille maximale de 2MB pour les images
        ]);

        // Préparation des données à mettre à jour, en excluant potentiellement l'image
        $input = $request->except('image', 'password');

        // Si un nouveau mot de passe est fourni, le hasher et l'ajouter aux données à sauvegarder
        if ($request->filled('password')) {
            $input['password'] = bcrypt($request->password);
        }

        // Si une nouvelle image est téléchargée, la traiter et la sauvegarder
if ($request->hasFile('image')) {
    $filenameWithExt = $request->file('image')->getClientOriginalName();
    $filenameWithoutExt = pathinfo($filenameWithExt, PATHINFO_FILENAME);
    $extension = $request->file('image')->getClientOriginalExtension();
    $filename = $filenameWithoutExt . '_' . time() . '.' . $extension;
    $path = $request->file('image')->storeAs('images', $filename, 'public');

    // Supprimer l'ancienne image si elle existe
    if ($file_temp) {
        Storage::disk('public')->delete('images/' . basename($file_temp));
    }

    // Mettre à jour le chemin de la nouvelle image dans les données à sauvegarder
    $input['image'] = '/storage/images/' . $filename;
}


        // Mettre à jour l'utilisateur avec les nouvelles données
        $user->update($input);

        // Construire l'URL de l'image mise à jour
        $imageUrl = asset($input['image']);

        // Ajouter l'URL de l'image à l'objet utilisateur mis à jour
        $user->image = $imageUrl;

        // Retourner une réponse JSON avec l'utilisateur mis à jour et un message de succès
        $responseData = [
            'user' => $user,
            'message' => 'User updated successfully'
        ];

        return response()->json($responseData, 200);
    }
    public function destroy(User $user)
    {
        $authenticatedUser = Auth::user();
    
        $user->delete();
    
        return response()->json(['message' => 'User deleted successfully']);
    }
    
        

    
}
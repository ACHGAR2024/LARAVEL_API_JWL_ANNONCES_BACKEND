<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    // Méthode pour récupérer toutes les catégories
    public function index()
    {
        $categories = Category::all();
        return response()->json(['categories' => $categories]);
    }

    // Méthode pour créer une nouvelle catégorie
    public function store(Request $request)
    {
        // Vérification que l'utilisateur est un administrateur
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Validation des données de la requête
        $request->validate([
            'nom' => 'required|string|max:255'
        ]);

        // Création de la catégorie
        $category = Category::create($request->all());

        return response()->json(['category' => $category, 'message' => 'Category created successfully']);
    }

    // Méthode pour afficher une catégorie spécifique
    public function show($id)
    {
        $category = Category::findOrFail($id);
        return response()->json(['category' => $category]);
    }

    // Méthode pour mettre à jour une catégorie
    public function update(Request $request, $id)
    {
        // Vérification que l'utilisateur est un administrateur
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Validation des données de la requête
        $request->validate([
            'nom' => 'required|string|max:255'
        ]);

        // Mise à jour de la catégorie
        $category = Category::findOrFail($id);
        $category->update($request->all());

        return response()->json(['category' => $category, 'message' => 'Category updated successfully']);
    }

    // Méthode pour supprimer une catégorie
    public function destroy($id)
    {
        // Vérification que l'utilisateur est un administrateur
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }
}
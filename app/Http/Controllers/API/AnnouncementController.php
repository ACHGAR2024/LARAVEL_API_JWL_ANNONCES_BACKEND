<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    // Méthode pour récupérer toutes les annonces
    public function index()
    {
        $announcements = Announcement::orderBy('created_at', 'desc')->get();
        return response()->json(['announcements' => $announcements]);
    }

    // Méthode pour créer une nouvelle annonce
    public function store(Request $request)
    {
        // Validation des données de la requête
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'user_id' => 'required|exists:users,id',
            'category_id' => 'required|exists:categories,id', // Validation de la catégorie
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'publication_date' => 'nullable|date',
            'place' => 'nullable|string|max:150', // Ajout de la validation pour place
        ]);

        $input = $request->except('photo');

        // Gestion de l'upload de l'image
        $filename = "";
        if ($request->hasFile('photo')) {
            $image = $request->file('photo');
            $name = time() . '_' . $image->getClientOriginalName();
            $filePath = $image->storeAs('images', $name, 'public');
            $input['photo'] = '/storage/' . $filePath;
        }

        // Création de l'annonce
        $announcement = Announcement::create($input);

        // Enregistrement de l'association avec la catégorie
        DB::table('ad_categories')->insert([
            'announcement_id' => $announcement->id,
            'category_id' => $request->category_id,
        ]);

        return response()->json(['announcement' => $announcement, 'message' => 'Announcement created successfully']);
    }

    // Méthode pour afficher une annonce spécifique
    public function show($id)
    {
        $announcement = Announcement::findOrFail($id);
        return response()->json(['announcement' => $announcement]);
    }

    // Méthode pour mettre à jour une annonce
    public function update(Request $request, $id)
    {
        // Récupération de l'annonce existante ou renvoie une erreur 404 si non trouvée
        $announcement = Announcement::findOrFail($id);
        $file_temp = $announcement->photo;

        // Validation des données de la requête
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'user_id' => 'required|exists:users,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'publication_date' => 'nullable|date',
            'place' => 'nullable|string|max:150', // Ajout de la validation pour place
        ]);

        // Préparation des données à mettre à jour, en excluant potentiellement la photo
        $input = $request->except('photo');

        // Si une nouvelle image est téléchargée, la traiter et la sauvegarder
        if ($request->hasFile('photo')) {
            $filenameWithExt = $request->file('photo')->getClientOriginalName();
            $filenameWithoutExt = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('photo')->getClientOriginalExtension();
            $filename = $filenameWithoutExt . '_' . time() . '.' . $extension;
            $path = $request->file('photo')->storeAs('images', $filename, 'public');

            // Supprimer l'ancienne image si elle existe
            if ($file_temp) {
                Storage::disk('public')->delete('images/' . basename($file_temp));
            }

            // Mettre à jour le chemin de la nouvelle image dans les données à sauvegarder
            $input['photo'] = '/storage/' . $path;
        }

        // Mettre à jour l'annonce avec les nouvelles données
        $announcement->update($input);

        // Retourner une réponse JSON avec l'annonce mise à jour et un message de succès
        return response()->json($announcement, 200);
    }

    // Méthode pour supprimer une annonce
    public function destroy($id)
    {
        $announcement = Announcement::findOrFail($id);
        Storage::disk('public')->delete('images/' . basename($announcement->photo));
        Announcement::destroy($id);
        return response()->json(['message' => 'Announcement deleted successfully']);
    }

    // Méthode pour récupérer les annonces par catégorie
    public function getAnnouncementsByCategory($categoryId)
    {
        $announcements = DB::table('announcements')
            ->join('ad_categories', 'announcements.id', '=', 'ad_categories.announcement_id')
            ->where('ad_categories.category_id', $categoryId)
            ->select('announcements.*')
            ->orderBy('announcements.created_at', 'desc')
            ->get();

        return response()->json(['announcements' => $announcements]);
    }
}
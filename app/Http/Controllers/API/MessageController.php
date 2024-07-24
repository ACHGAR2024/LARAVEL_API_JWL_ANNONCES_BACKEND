<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;



class MessageController extends Controller
{
    // Display a listing of messages.
    public function index()
    {
        $messages = Message::orderBy('created_at', 'desc')->get();
        return response()->json($messages);
    }

     // Méthode existante pour stocker un messagepublic function store(Request $request)
     public function store(Request $request)
     {
         $request->validate([
             'announcement_id' => 'required|integer',
             'content' => 'required|string',
             'status' => 'nullable|string|max:50', // Assurez-vous que le statut est valide
         ]);
     
         try {
            $message = Message::create([
                'user_id' => auth()->id(), // Utilise l'utilisateur authentifié
                'announcement_id' => $request->announcement_id,
                'content' => $request->content,
                'status' => isset($request->status) ? $request->status : 'En attente', // Valeur par défaut
            ]);
            
     
             return response()->json($message, 201);
         } catch (\Exception $e) {
             \Log::error('Erreur lors de l\'ajout du message: ' . $e->getMessage());
             return response()->json(['message' => 'Erreur serveur'], 500);
         }
     }
     
     public function addFavorite(Request $request)
{
    $request->validate([
        'announcement_id' => 'required|integer|exists:announcements,id',
        'content' => 'nullable|string',
        'status' => 'nullable|string|max:50'
    ]);

    try {
        $userId = Auth::id(); // Utiliser Auth pour obtenir l'ID utilisateur

        $message = Message::where('announcement_id', $request->announcement_id)
                          ->where('user_id', $userId)
                          ->first();

        if (!$message) {
            $message = Message::create([
                'user_id' => $userId,
                'announcement_id' => $request->announcement_id,
                'content' =>  ($request->content) ? $request->content : 'Favoris Ajouté',
                'status' => ($request->status) ? $request->status : 'Favoris Ajouté',
                'is_favorite' => true,
            ]);
        } else {
            $message->is_favorite = true;
            $message->content = ($request->content) ? $request->content : $message->content;
            $message->status = ($request->status) ? $request->status : $message->status;
            $message->save();
        }

        return response()->json(['message' => 'Message added to favorites'], 200);
    } catch (\Exception $e) {
        \Log::error('Erreur lors de l\'ajout aux favoris: ' . $e->getMessage());
        return response()->json(['message' => 'Erreur serveur'], 500);
    }
}

     
     // Nouvelle méthode pour signaler
     public function report(Request $request)
    {
        $request->validate([
            'announcement_id' => 'required|integer|exists:announcements,id',
            'content' => 'nullable|string',
            'status' => 'nullable|string|max:50'
        ]);

        try {
            $userId = Auth::id(); // Utiliser Auth pour obtenir l'ID utilisateur

            $message = Message::where('announcement_id', $request->announcement_id)
                              ->where('user_id', $userId)
                              ->first();

            if (!$message) {
                $message = Message::create([
                    'user_id' => $userId,
                    'announcement_id' => $request->announcement_id,
                    'content' => ($request->content) ? $request->content : 'Signalement Ajouté',
                    'status' => ($request->status) ? $request->status : 'Signalement Ajouté',
                    'is_report' => true,
                ]);
            } else {
                $message->is_report = true;
                $message->content = ($request->content) ? $request->content : $message->content;
                $message->status = ($request->status) ? $request->status : $message->status;
                $message->save();
            }

            return response()->json(['message' => 'Annonce signalée'], 200);
        } catch (\Exception $e) {
            \Log::error('Erreur lors du signalement: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur serveur'], 500);
        }
    }


    // Display the specified message.
    public function show($id)
    {
        $message = Message::findOrFail($id);
        return response()->json($message);
    }

    // Update the specified message in storage.
    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'exists:users,id',
            'announcement_id' => 'exists:announcements,id',
            'content' => 'string',
            'status' => 'string|max:50',
            'is_favorite' => 'boolean',
            'is_report' => 'boolean',
        ]);

        $message = Message::findOrFail($id);
        $message->update($request->all());
        return response()->json($message);
    }

    // Remove the specified message from storage.
    public function destroy($id)
    {
        $message = Message::findOrFail($id);
        $message->delete();
        return response()->json(null, 204);
    }
}
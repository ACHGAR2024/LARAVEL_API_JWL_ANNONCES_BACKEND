<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AnnouncementController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\PhotoController;
use App\Http\Controllers\API\MessageController; // Ajouter ce import

// Authentication routes
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login'])->name('login');

// Route pour récupérer l'utilisateur actuellement authentifié
Route::get('user/{user}', [UserController::class, 'indexprofil']);

// Routes des annonces (avec middleware auth:api pour protéger les routes nécessitant une authentification)
Route::middleware('auth:api')->group(function () {

    Route::post('announcements', [AnnouncementController::class, 'store']);
    Route::put('announcements/{announcement}', [AnnouncementController::class, 'update']);
    Route::delete('announcements/{announcement}', [AnnouncementController::class, 'destroy']);

    // Nouvelle route pour gérer les photos des annonces
    Route::post('announcements/{announcement}/photos', [PhotoController::class, 'store']);
    Route::get('announcements/{announcement}/photos', [PhotoController::class, 'index']);
    Route::get('announcements/{announcement}/photos/{photo}', [PhotoController::class, 'show']);
    Route::put('announcements/{announcement}/photos/{photo}', [PhotoController::class, 'update']);
    Route::delete('announcements/{announcement}/photos/{photo}', [PhotoController::class, 'destroy']);

    Route::post('categories', [CategoryController::class, 'store']);
    Route::put('categories/{category}', [CategoryController::class, 'update']);   
    Route::delete('categories/{category}', [CategoryController::class, 'destroy']);

    // Routes pour les messages
    Route::post('messages', [MessageController::class, 'store']);
    Route::post('messages/favorite', [MessageController::class, 'addFavorite']);
    Route::post('messages/report', [MessageController::class, 'report']);
    Route::get('messages', [MessageController::class, 'index']);
    Route::delete('messages/{message}', [MessageController::class, 'destroy']);

    Route::get('users', [AuthController::class, 'index']);
    Route::delete('users/{user}', [AuthController::class, 'destroy']);


    Route::post('logout', [AuthController::class, 'logout']);
    Route::put('update/{user}', [AuthController::class, 'update']);

    // Route pour récupérer l'utilisateur actuellement authentifié
    Route::get('user', [UserController::class, 'currentUser']);
});




    
// Routes des annonces accessibles publiquement
Route::get('announcements', [AnnouncementController::class, 'index']);
Route::get('announcements/{announcement}', [AnnouncementController::class, 'show']);
Route::get('announcements/category/{categoryId}', [AnnouncementController::class, 'getAnnouncementsByCategory']);

// Routes des catégories accessibles publiquement
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{category}', [CategoryController::class, 'show']);


// Routes des catégories protégées par auth:api et admin role
Route::middleware(['auth:api', 'role:admin'])->group(function () {
    // Vous pouvez ajouter des routes spécifiques aux admins ici
   
    
});
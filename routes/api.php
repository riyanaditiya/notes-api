<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\NoteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Notes CRUD
    Route::apiResource('notes', NoteController::class);

    // Soft Delete
    Route::get('/notes-trashed', [NoteController::class, 'trashed']);
    Route::patch('/notes/{id}/restore', [NoteController::class, 'restore']);
    Route::delete('/notes/{id}/force-delete', [NoteController::class, 'forceDelete']);

    Route::patch('/notes/{id}/favorite', [NoteController::class, 'toggleFavorite']);
});

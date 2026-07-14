<?php

use App\Http\Controllers\AnnotationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EtablissementController;
use App\Http\Controllers\FiliereController;
use App\Http\Controllers\MemoireController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UtilisateurController;
use App\Http\Controllers\VersionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminEcoleController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\CycleController;

/*
|--------------------------------------------------------------------------
| ROUTES PUBLIQUES (sans authentification)
|--------------------------------------------------------------------------
*/

Route::post('/login',    [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

/*
|--------------------------------------------------------------------------
| ROUTES PROTÉGÉES (Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // ---------------------------------------------------------------
    // UTILISATEURS
    // ---------------------------------------------------------------
    Route::prefix('utilisateurs')->group(function () {
        Route::get('',                    [UtilisateurController::class, 'index']);
        Route::post('',                   [UtilisateurController::class, 'store']);
        Route::get('{id}',                [UtilisateurController::class, 'show']);
        Route::put('{id}',                [UtilisateurController::class, 'update']);
        Route::delete('{id}',             [UtilisateurController::class, 'destroy']);
        Route::patch('{id}/toggle-actif', [UtilisateurController::class, 'toggleActif']);
    });

    // ---------------------------------------------------------------
    // ÉTABLISSEMENTS
    // ---------------------------------------------------------------
    Route::prefix('etablissements')->group(function () {
        Route::get('/',        [EtablissementController::class, 'index']);
        Route::post('/',       [EtablissementController::class, 'store']);
        Route::get('/{id}',    [EtablissementController::class, 'show']);
        Route::put('/{id}',    [EtablissementController::class, 'update']);
        Route::delete('/{id}', [EtablissementController::class, 'destroy']);
    });

    // ---------------------------------------------------------------
    // FILIÈRES
    // ---------------------------------------------------------------
    Route::prefix('filieres')->group(function () {
        Route::get('/',        [FiliereController::class, 'index']);
        Route::post('/',       [FiliereController::class, 'store']);
        Route::get('/{id}',    [FiliereController::class, 'show']);
        Route::put('/{id}',    [FiliereController::class, 'update']);
        Route::delete('/{id}', [FiliereController::class, 'destroy']);
    });

    // ---------------------------------------------------------------
    // CYCLES
    // ---------------------------------------------------------------
    Route::prefix('cycles')->group(function () {
        Route::get('/',        [CycleController::class, 'index']);
        Route::post('/',       [CycleController::class, 'store']);
        Route::get('/{id}',    [CycleController::class, 'show']);
        Route::put('/{id}',    [CycleController::class, 'update']);
        Route::delete('/{id}', [CycleController::class, 'destroy']);
    });

    // ---------------------------------------------------------------
    // MÉMOIRES
    // ---------------------------------------------------------------
    Route::prefix('memoires')->group(function () {
        Route::get('/',        [MemoireController::class, 'index']);
        Route::post('/',       [MemoireController::class, 'store']);
        Route::get('/{id}',    [MemoireController::class, 'show']);
        Route::put('/{id}',    [MemoireController::class, 'update']);
        Route::delete('/{id}', [MemoireController::class, 'destroy']);

        Route::patch('/{id}/statut',             [MemoireController::class, 'changerStatut']);
        Route::patch('/{id}/assigner-encadrant', [MemoireController::class, 'assignerEncadrant']);

        // Versions (imbriquées sous mémoire)
        Route::prefix('/{id_memoire}/versions')->group(function () {
            Route::get('/',                               [VersionController::class, 'index']);
            Route::post('/',                              [VersionController::class, 'store']);
            Route::get('/{id_version}',                   [VersionController::class, 'show']);
            Route::patch('/{id_version}/statut',          [VersionController::class, 'changerStatut']);
            Route::post('/{id_version}/remplacer',        [VersionController::class, 'remplacer']);
            Route::post('/{id_version}/verifier-plagiat', [VersionController::class, 'verifierPlagiat']);
        });
    });

    // ---------------------------------------------------------------
    // ANNOTATIONS
    // ---------------------------------------------------------------
    Route::prefix('versions/{id_version}/annotations')->group(function () {
        Route::get('/',  [AnnotationController::class, 'index']);
        Route::post('/', [AnnotationController::class, 'store']);
    });

    Route::prefix('annotations')->group(function () {
        Route::put('/{id}',    [AnnotationController::class, 'update']);
        Route::delete('/{id}', [AnnotationController::class, 'destroy']);
    });

    // ---------------------------------------------------------------
    // NOTIFICATIONS
    // ---------------------------------------------------------------
    Route::prefix('notifications')->group(function () {
        Route::get('/',              [NotificationController::class, 'index']);
        Route::get('/compteur',      [NotificationController::class, 'compteur']);
        Route::patch('/lire-tout',   [NotificationController::class, 'marquerToutLu']);
        Route::patch('/{id}/lire',   [NotificationController::class, 'marquerLue']);
    });

    // ---------------------------------------------------------------
    // CHECKLIST
    // ---------------------------------------------------------------
    Route::prefix('memoires/{id_memoire}/checklist')->group(function () {
        Route::get('/',                   [ChecklistController::class, 'index']);
        Route::post('/',                  [ChecklistController::class, 'store']);
        Route::patch('/{id_item}/toggle', [ChecklistController::class, 'toggle']);
        Route::delete('/{id_item}',       [ChecklistController::class, 'destroy']);
    });

    // ---------------------------------------------------------------
    // ADMIN ÉCOLE
    // ---------------------------------------------------------------
    Route::prefix('admin-ecoles')->group(function () {
        Route::get('/',                    [AdminEcoleController::class, 'index']);
        Route::post('/',                   [AdminEcoleController::class, 'store']);
        Route::get('/{id}',                [AdminEcoleController::class, 'show']);
        Route::put('/{id}',                [AdminEcoleController::class, 'update']);
        Route::patch('/{id}/toggle-actif', [AdminEcoleController::class, 'toggleActif']);
        Route::delete('/{id}',             [AdminEcoleController::class, 'destroy']);
    });

});
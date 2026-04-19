<?php

use App\Http\Controllers\Api\V1\ActivityController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\NoteController;
use Illuminate\Support\Facades\Route;

Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('dashboard', DashboardController::class);

    Route::get('auth/user', [AuthController::class, 'user']);
    Route::post('auth/logout', [AuthController::class, 'logout']);

    Route::get('clients/{client}/contacts', [ContactController::class, 'indexByClient']);
    Route::post('clients/{client}/contacts', [ContactController::class, 'store']);

    Route::get('clients/{client}/notes', [NoteController::class, 'indexByClient']);
    Route::post('clients/{client}/notes', [NoteController::class, 'store']);

    Route::get('clients/{client}/activities', [ActivityController::class, 'indexByClient']);
    Route::post('clients/{client}/activities', [ActivityController::class, 'store']);

    Route::post('clients/{client}/restore', [ClientController::class, 'restore'])->withTrashed();
    Route::apiResource('clients', ClientController::class);

    Route::apiResource('notes', NoteController::class)->except(['store']);
    Route::apiResource('activities', ActivityController::class)->except(['store']);

    Route::apiResource('contacts', ContactController::class)->except(['store']);
});

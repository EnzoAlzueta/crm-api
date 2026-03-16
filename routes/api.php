<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ClientController;

Route::apiResource('clients', ClientController::class);
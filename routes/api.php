<?php

use App\Http\Controllers\Api\V1\AuthenticationController;
use Illuminate\Support\Facades\Route;

Route::post('/logout', [AuthenticationController::class, 'logout'])->middleware('auth:sanctum');

include __DIR__.'/api/v1.php';

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::post('/users', [UserController::class, 'createUser']);
Route::get('/users/status', [UserController::class, 'changeUserStatus']);
Route::get('/users/distance', [UserController::class, 'getDistance']);
Route::get('/users/listing', [UserController::class, 'getUserListing']);

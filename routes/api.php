<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ReceipController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\IngredientController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
Route::resource('users', UserController::class);
Route::resource('categories', CategoryController::class);
Route::resource('receips', ReceipController::class);
Route::resource('ingredients', IngredientController::class);

Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/register', 'register');
    Route::post('/refresh', 'refresh');
});

<?php

use App\Http\Controllers\API\KorpaController;
use App\Http\Controllers\API\NarudzbineController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\RegisterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [RegisterController::class, 'login']);
Route::get('view-products', [ProductController::class, 'getAll']);
Route::get('get_products_admin', [ProductController::class, 'getAllAdminView']);
Route::get('search/{kriterijum}/{key}', [ProductController::class, 'search']);
Route::post('korpa_dodaj', [KorpaController::class, 'dodaj']);
Route::get('korpa', [KorpaController::class, 'getKorpa']);
Route::delete('obrisi_item/{id}', [KorpaController::class, 'obrisi']);
Route::post('naruci', [NarudzbineController::class, 'naruci']);
Route::post('velicine-za-boju', [ProductController::class, 'velicineZaBoju']);

Route::middleware(['auth:sanctum', 'isAPIAdmin'])->group(function () {
    Route::get('/checkingAuthenticated', function () {
        return response()->json(['message' => 'Authenticated', 'status' => 200], 200);
    });

    Route::post('addproduct', [ProductController::class, 'add']);
    Route::get('edit-product/{id}', [ProductController::class, 'edit']);
    Route::post('update-product/{id}', [ProductController::class, 'update']);
    Route::delete('deleteproduct/{id}', [ProductController::class, 'delete']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('logout', [RegisterController::class, 'logout']);
});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

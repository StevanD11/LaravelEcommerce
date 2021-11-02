<?php

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



Route::middleware(['auth:sanctum', 'isAPIAdmin'])->group(function () {

    Route::get('/checkingAuthenticated', function () {
        return response()->json(['message' => 'Authenticated', 'status' => 200], 200);
    });
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('logout', [RegisterController::class, 'logout']);
});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
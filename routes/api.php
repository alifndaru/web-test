<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\NewsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);


// Route::get('/news', [NewsController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('news/{news}/activity-log', [NewsController::class, 'getActivityLog']);
    Route::apiResource('news', NewsController::class);
    Route::post('/logout', [AuthController::class, 'logout']);
});
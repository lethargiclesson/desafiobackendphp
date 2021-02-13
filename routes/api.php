<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecommendationController;
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

/**
 * http://127.0.0.1:8000/api/recommend?city=Montes Claros
 * http://127.0.0.1:8000/api/recommend?lat=-16.735&long=-43.8617
 */

Route::get('recommend', [RecommendationController::class, 'recommended']);
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\RoomController;

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

//Authentication is not required for these endpoints
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

//Authentication is required for these endpoints (apply middleware auth:sanctum)
Route::group(['middleware' => ["auth:sanctum"]], function () {
    Route::get('logout', [AuthController::class, 'logout']);

    Route::controller(PersonController::class)->group(function () {
        Route::get('profile', 'profile');
    });

    Route::prefix('room')->controller(RoomController::class)->group(function () {
        Route::put('clean/{id}', 'clean');
        Route::put('lock/{id}', 'lock');
        Route::put('enable/{id}', 'enable');
        Route::get('pendient', 'getPendientRoomsByPersonId');
        Route::get('all', 'getRoomsByPersonId');
        Route::get('incidence', 'getIncidencesByPersonId');
        Route::get('all/enabled', 'getRooms');
    });
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

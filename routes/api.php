<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

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

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
});

// User Controller GROUP
Route::controller(UserController::class)->group(function () {
    Route::post('recover-password', 'recover_password_user');
});

Route::group(['middleware' => ['jwt.verify']], function () {
    Route::post('logout', [AuthController::class, 'logout']);
    
    // User Controller
    Route::post('users/update', [UserController::class, 'update']);
    Route::get('users/{id}', [UserController::class, 'show']);
    Route::post('users/update/profile/picture', [UserController::class, 'update_profile_picture']);

    // Reservation Controller
    Route::post('reservations', [ReservationController::class, 'store']);
});

// Clear cache
Route::get('/clear-cache', function() {
    Artisan::call('config:clear');
    Artisan::call('optimize');

    return response()->json([
        "message" => "Cache cleared successfully"
    ]);
});
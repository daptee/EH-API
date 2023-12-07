<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\UserController;
use App\Mail\confirmReservationMailable;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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

// Reservation Controller
Route::post('reservations', [ReservationController::class, 'store']);
Route::post('reservations/confirm', [ReservationController::class, 'confirm_reservation']);
Route::post('reservations/payment/rejection', [ReservationController::class, 'payment_rejection']);
Route::post('reservations/cancel', [ReservationController::class, 'cancel_reservation']);

Route::group(['middleware' => ['jwt.verify']], function () {
    Route::post('logout', [AuthController::class, 'logout']);
    
    // User Controller
    Route::post('users/update', [UserController::class, 'update']);
    Route::get('users/{id}', [UserController::class, 'show']);
    Route::post('users/update/profile/picture', [UserController::class, 'update_profile_picture']);

});

// Clear cache
Route::get('/clear-cache', function() {
    Artisan::call('config:clear');
    Artisan::call('optimize');

    return response()->json([
        "message" => "Cache cleared successfully"
    ]);
});

Route::get('test_mail',  function(){
    $data = [
        'reservation_number' => '123',
        'name' => 'test',
        'last_name' => 'test',
        'check_in' => 'test',
        'check_out' => 'test',
        'number_of_passengers' => 4,
        'email' => 'test',
    ];

    try {
        // Mail::to('sl.larramendy@gmail.com')->send(new confirmReservationMailable($data));
        Mail::to('enzo100amarilla@gmail.com')->send(new confirmReservationMailable($data));
    } catch (Exception $error) {
        Log::debug([
            "message"=> "Proceso correcto. Error en envio de mail",
            "line" => $error->getLine(),
            "error" => $error->getMessage(),
        ]);

        return response([
            "message" => "Proceso correcto. Error al enviar resumen a su casilla de mail",
            "status" => 600,
            "error" => $error->getMessage(),
        ], 200);
    }
});
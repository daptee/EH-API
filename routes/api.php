<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\InternalApiController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\UserController;
use App\Mail\confirmReservationMailable;
use App\Mail\TestMail;
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
    Route::post('login_super_admin', 'login_super_admin');
    // Route::post('register', 'register');
});

// User Controller GROUP
Route::controller(UserController::class)->group(function () {
    Route::post('recover-password', 'recover_password_user');
});

Route::post('room/images', [RoomController::class, 'store']);
Route::get('room/images/{room_id}', [RoomController::class, 'room_images']);
Route::get('room/images', [RoomController::class, 'all_images_rooms']);
Route::post('room/images/delete/{image_id}', [RoomController::class, 'room_images_delete']);
Route::get('room/images_principal', [RoomController::class, 'room_images_principal']);

// Reservation Controller
Route::post('reservations', [ReservationController::class, 'store']);
Route::post('reservations/confirm', [ReservationController::class, 'confirm_reservation']);
Route::post('reservations/payment/rejection', [ReservationController::class, 'payment_rejection']);
Route::post('reservations/cancel', [ReservationController::class, 'cancel_reservation']);
Route::get('reservations/status/list', [ReservationController::class, 'get_status_list']);
Route::get('reservations/by/reservation_number/{reservation_number}', [ReservationController::class, 'by_reservation_number']);

// Form controller
Route::post('form/contact', [FormController::class, 'form_contact']);

// Newsletter
Route::post('newsletter/register/email', [NewsletterController::class, 'newsletter_register_email']);

// Mail send code
Route::post('send/code/email', [UserController::class, 'send_code_email']);

// Mail matriz design
Route::post('matriz-design/send-form', [FormController::class, 'matriz_design']);

Route::group(['middleware' => ['jwt.verify']], function () {
    Route::post('logout', [AuthController::class, 'logout']);

    // User Controller
    // Route::post('users/update', [UserController::class, 'update']);
    // Route::get('users/{id}', [UserController::class, 'show']);
    // Route::post('users/update/profile/picture', [UserController::class, 'update_profile_picture']);

});

Route::prefix('internal-api-eh')->controller(InternalApiController::class)->group(function () {
    Route::get('/Naciones', 'Naciones');
    Route::get('/Naciones2', 'Naciones2');
    Route::get('/Tarifas', 'Tarifas');
    Route::get('/Disponibilidad', 'Disponibilidad');
    Route::get('/ReservaxCodigo', 'ReservaxCodigo');
    Route::get('/PedidoxCodigo', 'PedidoxCodigo');
    Route::get('/Articulos', 'Articulos');
    Route::get('/Articulo', 'Articulo');
    Route::get('/Rubros', 'Rubros');
    Route::get('/ArticulosDestacados', 'ArticulosDestacados');
    Route::get('/TiposDocumentos', 'TiposDocumentos');
    Route::get('/Pedidos', 'Pedidos');
    Route::get('/Habitaciones', 'Habitaciones');
    Route::get('/Reservas', 'Reservas');
    Route::get('/Calendario', 'Calendario');
    Route::post('/IniciaReserva', 'IniciaReserva');
    Route::post('/CancelaReserva', 'CancelaReserva');
    Route::post('/ConfirmaReserva', 'ConfirmaReserva');
    Route::post('/ConfirmaPasajeros', 'ConfirmaPasajeros');
    Route::post('/IniciaPedido', 'IniciaPedido');
    Route::post('/CancelaPedido', 'CancelaPedido');
    Route::post('/ConfirmaPedido', 'ConfirmaPedido');
    Route::post('/RealizaCheck', 'RealizaCheck');
    Route::get('/ReservaxOExterna', 'ReservaxOExterna');
    Route::get('/ReservaActiva', 'ReservaActiva');
});

// Route::get('getNewReservationsOTA', [ReservationController::class, 'getNewReservationsOTA']);

// Clear cache
Route::get('/clear-cache', function () {
    Artisan::call('config:clear');
    Artisan::call('optimize');

    return response()->json([
        "message" => "Cache cleared successfully"
    ]);
});

Route::get('test-mail', function () {
    try {
        $text = "Test de envio de mail EH";
        Mail::to("slarramendy@daptee.com.ar")->send(new TestMail("slarramendy@daptee.com.ar", $text));
        return 'Mail enviado';
    } catch (\Throwable $th) {
        Log::debug(print_r([$th->getMessage(), $th->getLine()],  true));
        return 'Mail no enviado';
    }
});

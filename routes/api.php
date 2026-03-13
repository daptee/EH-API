<?php

// use App\Http\Controllers\AgencyAuthController; // módulo no activo
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmergencyController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\InternalApiController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
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
    Route::post('login_super_admin', 'login_super_admin')->middleware('throttle:login');
    Route::post('verify_otp_super_admin', 'verify_otp_super_admin')->middleware('throttle:login');
    // Route::post('register', 'register');
});

// User Controller GROUP
Route::controller(UserController::class)->group(function () {
    Route::post('recover-password', 'recover_password_user')->middleware('throttle:mail_send');
});

// Rutas públicas de imágenes (lectura para el front)
Route::get('room/images/{room_id}', [RoomController::class, 'room_images']);
Route::get('room/images', [RoomController::class, 'all_images_rooms']);
Route::get('room/images_principal', [RoomController::class, 'room_images_principal']);

// Rutas de administración de imágenes (requieren autenticación)
Route::middleware(['jwt.verify', 'audit.log'])->group(function () {
    Route::post('room/images', [RoomController::class, 'store']);
    Route::post('room/images/delete/{image_id}', [RoomController::class, 'room_images_delete']);
});

// Reservation Controller
Route::post('reservations', [ReservationController::class, 'store']);
Route::post('reservations/confirm', [ReservationController::class, 'confirm_reservation']);
Route::post('reservations/payment/rejection', [ReservationController::class, 'payment_rejection']);
Route::post('reservations/cancel', [ReservationController::class, 'cancel_reservation']);
Route::get('reservations/status/list', [ReservationController::class, 'get_status_list']);
// Requiere autenticación: expone datos de reserva por número
Route::middleware(['jwt.verify', 'audit.log'])->get('reservations/by/reservation_number/{reservation_number}', [ReservationController::class, 'by_reservation_number']);

// Form controller
Route::post('form/contact', [FormController::class, 'form_contact']);

// Newsletter
Route::post('newsletter/register/email', [NewsletterController::class, 'newsletter_register_email']);

// Mail send code
Route::post('send/code/email', [UserController::class, 'send_code_email'])->middleware('throttle:mail_send');

// Mail matriz design
Route::post('matriz-design/send-form', [FormController::class, 'matriz_design']);

Route::group(['middleware' => ['jwt.verify', 'audit.log']], function () {
    Route::post('logout', [AuthController::class, 'logout']);

    // Emergency: reset masivo de contraseñas (solo super admin)
    Route::post('admin/emergency-reset-passwords', [EmergencyController::class, 'resetAllPasswords']);

    // User Controller
    // Route::post('users/update', [UserController::class, 'update']);
    // Route::get('users/{id}', [UserController::class, 'show']);
    // Route::post('users/update/profile/picture', [UserController::class, 'update_profile_picture']);

});

// Rutas públicas de internal-api-eh (sin autenticación)
Route::prefix('internal-api-eh')
    ->controller(InternalApiController::class)
    ->group(function () {
        Route::get('/Habitaciones', 'Habitaciones');
        Route::get('/Tarifas', 'Tarifas');
    });

Route::prefix('internal-api-eh')
    ->controller(InternalApiController::class)
    ->middleware(['jwt.verify', 'audit.log'])
    ->group(function () {
        Route::get('/Naciones', 'Naciones');
        Route::get('/Naciones2', 'Naciones2');
        Route::get('/Disponibilidad', 'Disponibilidad');
        Route::get('/ReservaxCodigo', 'ReservaxCodigo');
        Route::get('/PedidoxCodigo', 'PedidoxCodigo');
        Route::get('/Articulos', 'Articulos');
        Route::get('/Articulo', 'Articulo');
        Route::get('/Rubros', 'Rubros');
        Route::get('/ArticulosDestacados', 'ArticulosDestacados');
        Route::get('/TiposDocumentos', 'TiposDocumentos');
        Route::get('/Pedidos', 'Pedidos');
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
        Route::get('/Agencias', 'Agencias');
        Route::post('/CreaReservaAgencias', 'CreaReservaAgencias');
        Route::get('/ArticulosDesayunos', 'ArticulosDesayunos');
    });

// Rutas de agencia deshabilitadas temporalmente — módulo no activo
// Route::prefix('agency')->controller(AgencyAuthController::class)->group(function () {
//     Route::post('/register', 'register');
//     Route::post('/login', 'login');
//     Route::post('/recover-password', 'recover_password');
//     Route::post('/logout', 'logout')->middleware('jwt.verify');
//     Route::post('/profile/update', 'update_profile')->middleware('jwt.verify');
// });

// Route::get('getNewReservationsOTA', [ReservationController::class, 'getNewReservationsOTA']);

// TEMPORAL: sin auth — restaurar jwt.verify cuando se resuelva el problema de headers en este entorno
Route::get('/clear-cache', function () {
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('cache:clear');
    Artisan::call('optimize');
    return response()->json(["message" => "Cache cleared successfully"]);
});

// ─── TEMPORAL: endpoints de prueba de mails — ELIMINAR DESPUÉS ───────────────
// Body: { "to": "tucorreo@ejemplo.com" }
// confirm-reservation acepta también: { "type": "cliente" } o { "type": "admin" }

Route::post('/test/mail/confirm-reservation', function (\Illuminate\Http\Request $request) {
    $to   = $request->input('to');
    $type = $request->input('type', 'cliente');
    $data = [
        'reservation_number'   => 'RES-TEST-001',
        'name'                 => 'Juan',
        'last_name'            => 'Pérez',
        'email'                => $to,
        'phone'                => '+54 9 2966 123456',
        'check_in'             => '2026-03-20',
        'check_out'            => '2026-03-23',
        'number_of_passengers' => 2,
        'room_number'          => '101',
    ];
    Mail::to($to)->send(new \App\Mail\confirmReservationMailable($data, $type));
    return response()->json(['message' => "Mail confirm-reservation ($type) enviado a $to"]);
});

Route::post('/test/mail/send-code', function (\Illuminate\Http\Request $request) {
    $to   = $request->input('to');
    $data = [
        'name'               => 'Juan',
        'suite_name'         => 'Suite Premium',
        'room_number'        => '101',
        'code'               => '4521',
        'reservation_number' => 'RES-TEST-001',
    ];
    Mail::to($to)->send(new \App\Mail\SendCodeMail($data));
    return response()->json(['message' => "Mail send-code enviado a $to"]);
});

Route::post('/test/mail/matriz-design', function (\Illuminate\Http\Request $request) {
    $to   = $request->input('to');
    $data = [
        'name'               => 'Juan',
        'lastname'           => 'Pérez',
        'room_number'        => '101',
        'reservation_number' => 'RES-TEST-001',
        'email'              => $to,
        'phone'              => '+54 9 2966 123456',
        'text'               => 'Me gustaría reservar la cápsula Matriz para mañana a las 10hs.',
    ];
    Mail::to($to)->send(new \App\Mail\MatrizDesign($data));
    return response()->json(['message' => "Mail matriz-design enviado a $to"]);
});
// ─────────────────────────────────────────────────────────────────────────────

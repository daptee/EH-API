<?php

namespace App\Http\Controllers;

use App\Mail\confirmReservationMailable;
use App\Models\RejectedReservation;
use App\Models\Reservation;
use App\Models\ReservationStatus;
use App\Models\ReservationStatusHistory;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{

    public $model = User::class;
    public $s = "usuario";
    public $sp = "usuarios";
    public $ss = "usuario/s";
    public $v = "o";
    public $pr = "el";
    public $prp = "los";

    public function store(Request $request)
    {
        $request->validate([
            'reservation_number' => 'required',
        ]);

        $reservation = Reservation::where('reservation_number', $request->reservation_number)->first();
        if ($reservation)
            return response()->json(['message' => 'Reserva ya existente.'], 400);

        try {
            $reservation = new Reservation();
            $reservation->reservation_number = $request->reservation_number;
            $reservation->status_id = ReservationStatus::INICIADA;
            $reservation->save();

            ReservationStatusHistory::saveHistoryStatusReservation($reservation->id, ReservationStatus::INICIADA);
        } catch (Exception $error) {
            Log::debug("error al guardar reserva: " . $error->getMessage() . ' line: ' . $error->getLine());
            return response(["error" => $error->getMessage()], 500);
        }

        $reservation = Reservation::getAllReservation($reservation->id);

        return response()->json(['message' => 'Reserva guardada con exito.', 'reservation' => $reservation], 200);
    }

    public function confirm_reservation(Request $request)
    {
        $request->validate([
            "reservation_id" => ['required', 'integer', Rule::exists('reservations', 'id')],
            "user" => 'required',
        ]);

        $reservation = Reservation::find($request->reservation_id);

        try {
            DB::transaction(function () use ($request, $reservation) {

                $status_id = ReservationStatus::CONFIRMADA;

                // Actualizamos estado en reserva
                $reservation->status_id = $status_id;
                $reservation->save();

                // Almacenamos historial de estado en reserva
                ReservationStatusHistory::saveHistoryStatusReservation($request->reservation_id, $status_id);
            });
            $data = [
                'reservation_number' => $reservation->reservation_number,
                'name' => $request->user['name'],
                'last_name' => $request->user['last_name'],
                'check_in' => $request->user['check_in'],
                'check_out' => $request->user['check_out'],
                'number_of_passengers' => $request->number_of_passengers,
                'email' => $request->user['email'],
            ];

            try {
                Mail::to(config('mail.confirmation_email'))->send(new confirmReservationMailable($data));
                // Mail::to('enzo100amarilla@gmail.com')->send(new confirmReservationMailable($data));
            } catch (Exception $error) {
                Log::debug([
                    "message" => "Proceso correcto. Error en envio de mail",
                    "line" => $error->getLine(),
                    "error" => $error->getMessage(),
                ]);

                return response([
                    "message" => "Proceso correcto. Error al enviar resumen a su casilla de mail",
                    "status" => 600,
                    "error" => $error->getMessage(),
                ], 200);
            }
        } catch (Exception $error) {
            Log::debug([
                "error al confirmar reserva: " . $error->getMessage(),
                "line: " . $error->getLine(),
                "ID reserva: " . $request->reservation_id
            ]);
            return response(["error" => $error->getMessage()], 500);
        }

        $reservation = Reservation::getAllReservation($reservation->id);

        return response()->json(['message' => 'Reserva confirmada con exito.', 'reservation' => $reservation], 200);
    }

    public function payment_rejection(Request $request)
    {
        $request->validate([
            "reservation_id" => ['required', 'integer', Rule::exists('reservations', 'id')],
            "reason_rejection" => 'required',
        ]);

        $reservation = Reservation::find($request->reservation_id);

        try {
            DB::transaction(function () use ($request, $reservation) {

                $status_id = ReservationStatus::RECHAZADA;

                // Actualizamos estado en reserva
                $reservation->status_id = $status_id;
                $reservation->save();

                // Almacenamos razon del rechazo en reserva
                RejectedReservation::saveReasonRejection($request->reservation_id, $request->reason_rejection);

                // Almacenamos historial de estado en reserva
                ReservationStatusHistory::saveHistoryStatusReservation($request->reservation_id, $status_id);
            });
        } catch (Exception $error) {
            Log::debug([
                "error al rechazar reserva: " . $error->getMessage(),
                "line: " . $error->getLine(),
                "ID reserva: " . $request->reservation_id
            ]);
            return response(["error" => $error->getMessage()], 500);
        }

        $reservation = Reservation::getAllReservation($reservation->id);

        return response()->json(['message' => 'Reserva rechazada con exito.', 'reservation' => $reservation], 200);
    }

    public function cancel_reservation(Request $request)
    {
        $request->validate([
            "reservation_id" => ['required', 'integer', Rule::exists('reservations', 'id')],
        ]);

        $reservation = Reservation::find($request->reservation_id);

        try {
            DB::transaction(function () use ($request, $reservation) {

                $status_id = ReservationStatus::CANCELADA;

                // Actualizamos estado en reserva
                $reservation->status_id = $status_id;
                $reservation->save();

                // Almacenamos historial de estado en reserva
                ReservationStatusHistory::saveHistoryStatusReservation($request->reservation_id, $status_id);
            });
        } catch (Exception $error) {
            Log::debug([
                "error al cancelar reserva: " . $error->getMessage(),
                "line: " . $error->getLine(),
                "ID reserva: " . $request->reservation_id
            ]);
            return response(["error" => $error->getMessage()], 500);
        }

        $reservation = Reservation::getAllReservation($reservation->id);

        return response()->json(['message' => 'Reserva cancelada con exito.', 'reservation' => $reservation], 200);
    }

    public function get_status_list()
    {
        $reservation_states = null;
        try {
            $reservation_states = ReservationStatus::all();
        } catch (Exception $error) {
            Log::debug([
                "error al obtener listado de estados: " . $error->getMessage(),
                "line: " . $error->getLine()
            ]);
            return response(["error" => $error->getMessage()], 500);
        }

        return response()->json(['reservation_states' => $reservation_states], 200);
    }

    public function by_reservation_number($reservation_number)
    {
        $reservation = null;
        try {
            $reservation = Reservation::with(['status_history.status', 'rejected_history'])->where('reservation_number', $reservation_number)->first();
        } catch (Exception $error) {
            Log::debug([
                "error al obtener reserva: " . $error->getMessage(),
                "line: " . $error->getLine()
            ]);
            return response(["error" => $error->getMessage()], 500);
        }

        return response()->json(['reservation' => $reservation], 200);
    }
}

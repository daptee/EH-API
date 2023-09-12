<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\ReservationStatus;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

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
        
        try {
            $reservation = new Reservation();
            $reservation->reservation_number = $request->reservation_number;
            $reservation->status_id = ReservationStatus::INICIADA;
            $reservation->save();
            
        } catch (Exception $error) {
            return response(["error" => $error->getMessage()], 500);
        }
       
        $reservation = Reservation::getAllReservation($reservation->id);

        return response()->json(['message' => 'Reserva guardada con exito.', 'reservation' => $reservation], 200);
    }
}

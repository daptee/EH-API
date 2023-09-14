<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ReservationStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'reservations_status_history';

    public function status(): HasOne
    {
        return $this->hasOne(ReservationStatus::class, 'id', 'status_id');
    }

    public function reservation(): HasOne
    {
        return $this->hasOne(Reservation::class, 'id', 'reservation_id');
    }

    public static function saveHistoryStatusReservation($reservation_id, $status_id)
    {
        $reservation_status_history = new ReservationStatusHistory();
        $reservation_status_history->status_id = $status_id;
        $reservation_status_history->reservation_id = $reservation_id;
        $reservation_status_history->save();
    }
}

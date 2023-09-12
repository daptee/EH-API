<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Reservation extends Model
{
    use HasFactory;

    protected $table = 'reservations';

    public function status(): HasOne
    {
        return $this->hasOne(ReservationStatus::class, 'id', 'status_id');
    }

    public static function getAllReservation($id)
    {
        return Reservation::with('status')->find($id);
    }
}

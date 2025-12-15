<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Reservation extends Model
{
    use HasFactory;

    protected $table = 'reservations';

    public function status(): HasOne
    {
        return $this->hasOne(ReservationStatus::class, 'id', 'status_id');
    }

    public function agency_user(): HasOne
    {
        return $this->hasOne(AgencyUser::class, 'id', 'agency_user_id');
    }

    public function status_history(): HasMany
    {
        return $this->HasMany(ReservationStatusHistory::class, 'reservation_id', 'id');
    }

    public function rejected_history(): HasMany
    {
        return $this->HasMany(RejectedReservation::class, 'reservation_id', 'id');
    }

    public static function getAllReservation($id)
    {
        return Reservation::with('status')->find($id);
    }
}

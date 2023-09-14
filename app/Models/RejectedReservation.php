<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RejectedReservation extends Model
{
    use HasFactory;

    protected $table = 'rejected_reservations';

    public function reservation(): HasOne
    {
        return $this->hasOne(Reservation::class, 'id', 'reservation_id');
    }

    public static function saveReasonRejection($reservation_id, $reason)
    {
        $reason_rejection = new RejectedReservation();
        $reason_rejection->reservation_id = $reservation_id;
        $reason_rejection->data = $reason;
        $reason_rejection->save();
    }
}

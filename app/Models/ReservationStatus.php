<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationStatus extends Model
{
    use HasFactory;

    const INICIADA = 1;
    const CONFIRMADA = 2;
    const CANCELADA = 3;

    protected $table = 'reservations_status';

    protected $fillable = ['name'];
}

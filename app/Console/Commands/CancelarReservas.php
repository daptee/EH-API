<?php

namespace App\Console\Commands;

use App\Models\AuditReservation;
use App\Models\Reservation;
use App\Models\ReservationStatus;
use App\Models\UserReservation;
use App\Models\UserReservationStatusHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

// use Illuminate\Support\Facades\Http;

class CancelarReservas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cancelar:reservas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Este comando se encarga de cancelar reservas que quedaron pendientes (perdidas)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $reservations = Reservation::whereIn('status_id', [ReservationStatus::INICIADA])
                                    ->where('created_at', '<', now()->modify('-30 minute')->format('Y-m-d H:i:s'))
                                    ->get();
        
        // Log::debug($reservations);
        // Log::debug("Cantidad de reservas que trae la query: " . count($reservations));

        if(count($reservations) > 0){
            foreach($reservations as $reservation){
                $reservation->status_id = ReservationStatus::CANCELADO_AUTOMATICO;
                $reservation->save();
            }
        }
    }
}

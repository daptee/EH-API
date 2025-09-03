<?php

namespace App\Console\Commands;

use App\Models\AuditReservation;
use App\Models\Reservation;
use App\Models\ReservationStatus;
use App\Models\ReservationStatusHistory;
use App\Models\UserReservation;
use App\Models\UserReservationStatusHistory;
use Exception;
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

    public function get_url()
    {
        $environment = config("app.environment");
        return $environment === "DEV" ? "https://apieh.ehboutiqueexperience.com:9096" : "https://apieh.ehboutiqueexperience.com:8086";
    }

    private function transform_params($params)
    {
        // Recorremos los parámetros y convertimos null o valores vacíos a cadena vacía
        array_walk_recursive($params, function (&$value) {
            if (is_null($value) || $value === '') {
                $value = '';
            }
        });

        return $params;
    }

    public function cancel_reservation($params = [])
    {
        try {
            $url = $this->get_url();

            // Transformar los parámetros antes de enviarlos
            $params = $this->transform_params($params);

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, "$url/CancelaReserva"); // URL de la API
            curl_setopt($ch, CURLOPT_POST, 1); // Indicar que es una petición POST
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params)); // Pasar los datos a enviar
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Para recibir la respuesta como string
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json',
            ]);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);
                curl_close($ch);
                Log::debug(["error_response_cancels_reservation" => $error_msg]);
                return ['status' => 500, 'error' => $error_msg];
            }

            // ✅ acá obtenés el código real de la respuesta
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);

            $decodedResponse = json_decode($response, true);

            // Verificar si el resultado indica error
            if (isset($decodedResponse['RESULT']) && $decodedResponse['RESULT'] === 'ERROR') {
                Log::debug([
                    "error_response_cancels_reservation" => $decodedResponse,
                    "http_code" => $httpCode
                ]);
                return ['status' => $httpCode, 'error' => $decodedResponse];
            }

            return ['status' => $httpCode, 'decodedResponse' => $decodedResponse];

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            Log::debug(["error_response_cancels_reservation" => $e->getMessage()]);
            return ['status' => 500, 'error' => $e->getMessage()];
        }
    }

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

                $params = [
                    "RSV" => $reservation->reservation_number
                ];
                
                $response = $this->cancel_reservation($params);
                $response_log = json_encode($response);

                Log::debug("Numero de reserva: $reservation->reservation_number , Resultado API: $response_log");
                
                $reservation_number = $reservation->reservation_number; 

                if($response['status'] == 200){
                    try {
                        $status_id = ReservationStatus::CANCELADO_AUTOMATICO;

                        $reservation->status_id = $status_id;
                        $reservation->save();
    
                        ReservationStatusHistory::saveHistoryStatusReservation($reservation->id, $status_id);
                        Log::debug("Numero de reserva: $reservation->reservation_number , Resultado: Reserva cancelada con exito.");                       
                    } catch (Exception $e) {
                        $error = $e->getMessage();
                        Log::debug("Numero de reserva: $reservation->reservation_number , Resultado: Error al cancelar reserva, Message: $error");                       
                    }
                }else if($response['status'] == 400 && $response['error']['ERROR_MSG'] == "RESERVA $reservation_number NO EXISTE"){
                    try {
                        $status_id = ReservationStatus::CANCELADO_AUTOMATICO;

                        $reservation->status_id = $status_id;
                        $reservation->save();
    
                        ReservationStatusHistory::saveHistoryStatusReservation($reservation->id, $status_id);
                        Log::debug("Numero de reserva: $reservation_number , Resultado: Reserva cancelada con exito.");                       
                    } catch (Exception $e) {
                        $error = $e->getMessage();
                        Log::debug("Numero de reserva: $reservation_number , Resultado: Error al cancelar reserva, Message: $error");                       
                    }
                }
            }
        }
    }
}

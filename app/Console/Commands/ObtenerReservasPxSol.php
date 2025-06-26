<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ObtenerReservasPxSol extends Command
{
    protected $signature = 'obtener-reservas:pxsol';
    protected $description = 'Este comando obtiene las reservas de pxsol';

    public function handle()
    {
        $this->info('Iniciando sincronización de reservas...');
        $bookings = $this->fetchAllBookings();

        $payloads = [];
        $array_rooms = [
            '135365' => 4,
            '135364' => 3,
            '115361' => 1,
            '135366' => 5,
            '135348' => 2,
            '135367' => 6,
            '135368' => 7,
        ];

        Log::debug("Iniciando procesamiento de reservas. Cantidad de reservas a procesar: " . count($bookings));

        foreach ($bookings as $booking) {
            $bookingId = $booking['booking_id'];
            $this->info("Procesando booking_id: $bookingId");

            $detail = $this->fetchBookingDetail($bookingId);

            if (!$detail) {
                $this->warn("No se pudo obtener el detalle para ID $bookingId");
                continue;
            }

            $checkin = \Carbon\Carbon::parse($booking['check_in'])->format('d/m/Y');
            $checkout = \Carbon\Carbon::parse($booking['check_out'])->format('d/m/Y');
            $bookingId = $booking['booking_id'];

            $rooms = $detail['relationships']['rooms']['data'] ?? [];
            $guests = $detail['relationships']['guests']['data']['attributes'][0] ?? [];
            $guestDetails = $booking['guest_details'] ?? [];
            $way = $detail['attributes']['way'] ?? null;
            $medio = $detail['attributes']['medio'] ?? null;

            $platform = ($way === 'OTA') ? $this->cleanPlatform($medio) : null;

            foreach ($rooms as $room) {
                $adults = (int) $room['adults'] ?? 0;
                $children = (int) $room['children'] ?? 0;
                $babies = (int) $room['babies'] ?? 0;
                $cuantos = $adults + $children + $babies;
                $room_id = $room['room_id'];

                $payloads[] = [
                    "DESDE" => $checkin,
                    "HASTA" => $checkout,
                    "HAB" =>  $array_rooms[$room_id] ?? null, // Completar si aplica mapping de habitación
                    "CUANTOS" => $cuantos,
                    "RESERVA_PXSOL" => $bookingId,
                    "PAX" => trim(($guestDetails['name'] ?? '') . ' ' . ($guestDetails['last_name'] ?? '')),
                    "TELEFONO_CONTACTO" => $guests['phone'] ?? '',
                    "EMAIL_CONTACTO" => $guestDetails['email'] ?? '',
                    "EMAIL_NOTIFICACIONES" => $guestDetails['email'] ?? '',
                    // "VOL_ORDEN" => null,
                    "IMPORTE_COBRADO" => number_format((float) $detail['attributes']['subtotal'], 2, ',', ''),
                    "IMPORTE_ADICIONAL" => number_format((float) $detail['attributes']['taxes'], 2, ',', ''),
                    // "TRANSACCION_NRO" => null,
                    // "FAC_A_CUIT" => null,
                    // "FAC_A_RSOCIAL" => null,
                    // "FAC_A_SFISCAL" => null,
                    // "DNICUIT" => null,
                    // "DNICUIT_TIPO" => null,
                    "ORIGEN_WEB" => "PXSOL",
                    "PLATAFORMA_EXTERNA" => $platform,
                    // "ORDEN_EXTERNA" => null,
                ];
            }
        }

        Log::debug($payloads);
        $this->fetchDataFromApi('IniciaReservaPX', $payloads, 'POST');
        $this->info('✅ Sincronización finalizada.');
    }

    public function get_url()
    {
        $environment = config("app.environment");
        return $environment === "DEV" ? "https://apieh.ehboutiqueexperience.com:9096" : "https://apieh.ehboutiqueexperience.com:8086";
    }

    protected function fetchAllBookings()
    {
        $allBookings = [];
        $page = 1;

        do {
            $response = Http::withToken(config('services.pxsol_api_key_token'))
                ->get('https://gateway-prod.pxsol.com/v2/booking/list', ['page' => $page]);

            if (!$response->successful()) {
                $this->error("Error al obtener página $page: " . $response->body());
                break;
            }

            $data = $response[0];
            $items = $data['data'] ?? [];
            $meta = $data['meta'] ?? [];

            $allBookings = array_merge($allBookings, $items);
            $page++;
        } while (($meta['current_page'] ?? 1) < ($meta['last_page'] ?? 1));

        return $allBookings;
    }

    protected function fetchBookingDetail($bookingId)
    {
        $response = Http::withToken(config('services.pxsol_api_key_token'))
            ->get('https://gateway-prod.pxsol.com/v2/booking/info', ['id' => $bookingId]);

        if ($response->successful()) {
            return $response->json()['data'] ?? null;
        }

        return null;
    }

    protected function cleanPlatform($medio)
    {
        return str_ends_with($medio, '.com')
            ? ucfirst(str_replace('.com', '', $medio))
            : ucfirst($medio);
    }

    public function fetchDataFromApi($endpoint, $params = [], $method = 'GET')
    {
        try {
            $url = $this->get_url();
            $client = new \GuzzleHttp\Client(['base_uri' => $url, 'verify' => false]);

            // Transformar los parámetros antes de enviarlos
            $params = $this->transformParams($params);

            if (strtoupper($method) === 'POST') {
                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, "$url/$endpoint"); // URL de la API
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
                    Log::debug([
                        "response" => $error_msg,
                    ]);
                    return response()->json(['error' => $error_msg], 500);
                }

                curl_close($ch);

                $decodedResponse = json_decode($response, true);
                Log::debug([
                    "response" => $decodedResponse,
                ]);

                // Verificar si el resultado indica error
                if (isset($decodedResponse['RESULT']) && $decodedResponse['RESULT'] === 'ERROR') {
                    return response()->json($decodedResponse, 400);
                }

                return $decodedResponse;
            } else {
                $response = $client->get("/$endpoint", [
                    'query' => $params // Enviamos los parámetros en formato GET
                ]);
            }

            if ($response->getStatusCode() == 200) {
                $decodedResponse = json_decode($response->getBody()->getContents(), true);

                // Verificar si el resultado indica error
                if (isset($decodedResponse['RESULT']) && $decodedResponse['RESULT'] === 'ERROR') {
                    return response()->json($decodedResponse, 400);
                }

                return $decodedResponse;
            } else {
                return $response->getBody()->getContents();
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            // Retornamos un mensaje de error más claro
            return response()->json([
                'error' => 'Error al conectar con la API',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function transformParams($params)
    {
        // Recorremos los parámetros y convertimos null o valores vacíos a cadena vacía
        array_walk_recursive($params, function (&$value) {
            if (is_null($value) || $value === '') {
                $value = '';
            }
        });

        return $params;
    }
}

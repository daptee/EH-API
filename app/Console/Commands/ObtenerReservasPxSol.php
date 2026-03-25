<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ObtenerReservasPxSol extends Command
{
    protected $signature = 'obtener-reservas:pxsol';
    protected $description = 'Este comando obtiene las reservas de pxsol';

    public function handle()
    {
        // $this->info('Iniciando sincronización de reservas...');

        // Reservas nuevas: filtrar por fecha de creación (últimos 5 días)
        $newBookings = $this->fetchAllBookings([
            'created_from' => now()->subDays(5)->format('Y-m-d'),
            'created_to'   => now()->format('Y-m-d'),
        ]);

        // Cancelaciones: filtrar por fecha de actualización (últimos 2 días)
        // Una reserva cancelada actualiza su updated_at independientemente de cuándo fue creada
        $updatedBookings = $this->fetchAllBookings([
            'updated_from' => now()->subDays(2)->format('Y-m-d'),
            'updated_to'   => now()->format('Y-m-d'),
        ]);

        // Unificar evitando duplicados por booking_id
        $allBookingsById = [];
        foreach ($newBookings as $b) {
            $allBookingsById[$b['booking_id']] = ['booking' => $b, 'process' => 'new'];
        }
        foreach ($updatedBookings as $b) {
            if (!isset($allBookingsById[$b['booking_id']])) {
                // Solo aparece en updated → es una cancelación de reserva antigua
                $allBookingsById[$b['booking_id']] = ['booking' => $b, 'process' => 'cancellation_only'];
            }
        }

        $bookings = array_column($allBookingsById, 'booking');
        $processMap = [];
        foreach ($allBookingsById as $id => $entry) {
            $processMap[$id] = $entry['process'];
        }

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

        Log::channel('pxsol')->debug("Iniciando procesamiento de reservas. Cantidad: " . count($bookings));
        // Log::debug("Iniciando procesamiento de reservas. Cantidad de reservas a procesar: " . count($bookings));

        foreach ($bookings as $booking) {
            $bookingId = $booking['booking_id'];
            // Log::channel('pxsol')->debug("Procesando booking_id: $bookingId", [
            //     'booking' => $booking
            // ]);
            // $this->info("Procesando booking_id: $bookingId");

            $detail = $this->fetchBookingDetail($bookingId);

            if (!$detail) {
                Log::channel('pxsol')->warning("No se pudo obtener el detalle para ID $bookingId");
                // $this->warn("No se pudo obtener el detalle para ID $bookingId");
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
                $status_id = $booking['status'];

                // Las reservas que vienen solo del filtro updated (cancellation_only)
                // no deben procesarse como nuevas aunque tengan status 3
                $process = $processMap[$bookingId] ?? 'new';

                if ($status_id == 1) {
                    try {
                        $params = [
                            'RSVPX' => $bookingId,
                            'HAB' => $array_rooms[$room_id] ?? null
                        ];

                        if (is_null($params['HAB'])) {
                            Log::channel('pxsol')->warning("No hay mapeo de habitación para ID PxSol $room_id - Reserva $bookingId");
                        }

                        Log::channel('pxsol')->info("Iniciando CANCELACIÓN para reserva $bookingId - Hab: " . ($params['HAB'] ?? $room_id));
                        $response = $this->fetchDataFromApi('CancelaReservaPX', $params, 'POST');

                        Log::channel('pxsol')->debug("Resultado CANCELA RESERVA $bookingId:", [
                            'params_sent' => $params,
                            'response_received' => $response
                        ]);
                    } catch (Exception $e) {
                        Log::channel('pxsol')->error("Excepción al cancelar reserva $bookingId:", [
                            'RSVPX' => $bookingId,
                            'HAB' => $array_rooms[$room_id] ?? 'N/A',
                            'message' => $e->getMessage()
                        ]);
                    }
                } else if ($status_id == 3 && $process !== 'cancellation_only') {
                    $payload = [
                        "DESDE" => $checkin,
                        "HASTA" => $checkout,
                        "HAB" => $array_rooms[$room_id] ?? null,
                        "CUANTOS" => $cuantos,
                        "RESERVA_PXSOL" => $bookingId,
                        "PAX" => trim(($guestDetails['name'] ?? '') . ' ' . ($guestDetails['last_name'] ?? '')),
                        "TELEFONO_CONTACTO" => $guests['phone'] ?? '',
                        "EMAIL_CONTACTO" => $guestDetails['email'] ?? '',
                        "EMAIL_NOTIFICACIONES" => $guestDetails['email'] ?? '',
                        "IMPORTE_COBRADO" => number_format((float) ($detail['attributes']['subtotal'] ?? 0), 2, ',', ''),
                        "IMPORTE_ADICIONAL" => number_format((float) ($detail['attributes']['taxes'] ?? 0), 2, ',', ''),
                        "ORIGEN_WEB" => "PXSOL",
                        "PLATAFORMA_EXTERNA" => $platform,
                    ];

                    try {
                        Log::channel('pxsol')->info("Iniciando CREACIÓN (IniciaReservaPX) para reserva $bookingId - Hab: " . ($payload['HAB'] ?? $room_id));
                        $response = $this->fetchDataFromApi('IniciaReservaPX', $payload, 'POST');

                        Log::channel('pxsol')->debug("Resultado INICIA RESERVA $bookingId:", [
                            'payload_sent' => $payload,
                            'response_received' => $response
                        ]);
                    } catch (Exception $e) {
                        Log::channel('pxsol')->error("Excepción al crear reserva $bookingId:", [
                            'payload' => $payload,
                            'message' => $e->getMessage()
                        ]);
                    }
                }
            }
        }

        Log::channel('pxsol')->info("Procesamiento de reservas PXSOL finalizado.");
    }

    public function get_url()
    {
        $environment = config("app.environment");
        return $environment === "DEV" ? "https://apieh.ehboutiqueexperience.com:9096" : "https://apieh.ehboutiqueexperience.com:8086";
    }

    protected function fetchAllBookings(array $dateFilters = [])
    {
        $allBookings = [];
        $page = 1;

        do {
            $response = Http::withToken(config('services.pxsol_api_key_token'))
                ->get('https://gateway-prod.pxsol.com/v2/booking/list', array_merge(
                    ['current_page' => $page],
                    $dateFilters
                ));

            if (!$response->successful()) {
                Log::channel('pxsol')->error("Error al obtener página $page", [
                    'body' => $response->body()
                ]);
                // $this->error("Error al obtener página $page: " . $response->body());
                break;
            }

            $data = $response[0];
            $items = $data['data'] ?? [];
            $meta = $data['meta'] ?? [];

            $allBookings = array_merge($allBookings, $items);
            $page++;
        } while (($meta['current_page'] ?? 1) < ($meta['last_page'] ?? 1));

        Log::channel('pxsol')->info("Total de reservas obtenidas", [
            'count'   => count($allBookings),
            'filters' => $dateFilters,
        ]);

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
                    Log::channel('pxsol')->error("Error CURL ejecutando $endpoint:", [
                        'params' => $params,
                        'message' => $error_msg,
                    ]);
                    return ['RESULT' => 'ERROR', 'MESSAGE' => $error_msg];
                }

                curl_close($ch);

                $decodedResponse = json_decode($response, true);

                // Verificar si el resultado indica error
                if (isset($decodedResponse['RESULT']) && $decodedResponse['RESULT'] === 'ERROR') {
                    Log::channel('pxsol')->error("API respondió con ERROR en $endpoint:", [
                        'params' => $params,
                        'response' => $decodedResponse,
                    ]);
                } else {
                    Log::channel('pxsol')->info("API respondió con ÉXITO en $endpoint");
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
                    Log::channel('pxsol')->error("API respondió con ERROR en $endpoint:", [
                        'params' => $params,
                        'response' => $decodedResponse,
                    ]);
                }

                return $decodedResponse;
            } else {
                return $response->getBody()->getContents();
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            Log::channel('pxsol')->error("Excepción Guzzle en $endpoint: " . $e->getMessage());
            return ['RESULT' => 'ERROR', 'MESSAGE' => $e->getMessage()];
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

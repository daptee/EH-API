<?php

namespace App\Http\Controllers;

use App\Models\UserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PxsolController extends Controller
{
    private array $roomMap = [
        '135365' => 4,
        '135364' => 3,
        '115361' => 1,
        '135366' => 5,
        '135348' => 2,
        '135367' => 6,
        '135368' => 7,
    ];

    /**
     * Procesa manualmente cancelaciones de PXSOL para un rango de días hacia atrás.
     * Solo accesible por super admin. No afecta el proceso automático.
     *
     * Body: { "days": 30 }
     */
    public function processCancellations(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        $user = Auth::user();
        if ($user->user_type_id !== UserType::SUPERADMIN) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $days = (int) $request->days;
        $updatedFrom = now()->subDays($days)->format('Y-m-d');
        $updatedTo   = now()->format('Y-m-d');

        Log::channel('pxsol')->info("MANUAL processCancellations iniciado", [
            'user'         => $user->email,
            'days'         => $days,
            'updated_from' => $updatedFrom,
            'updated_to'   => $updatedTo,
        ]);

        $bookings = $this->fetchBookings($updatedFrom, $updatedTo);

        $results = [
            'range'      => "updated_from: $updatedFrom — updated_to: $updatedTo",
            'found'      => 0,
            'processed'  => [],
            'skipped'    => [],
            'errors'     => [],
        ];

        foreach ($bookings as $booking) {
            if ((int) $booking['status'] !== 1) {
                continue;
            }

            $bookingId = $booking['booking_id'];
            $results['found']++;

            $detail = $this->fetchBookingDetail($bookingId);
            if (!$detail) {
                $results['errors'][] = ['booking_id' => $bookingId, 'reason' => 'No se pudo obtener detalle'];
                continue;
            }

            $rooms = $detail['relationships']['rooms']['data'] ?? [];

            foreach ($rooms as $room) {
                $roomId    = $room['room_id'];
                $internalHab = $this->roomMap[$roomId] ?? null;

                $params = [
                    'RSVPX' => $bookingId,
                    'HAB'   => $internalHab,
                ];

                if (is_null($internalHab)) {
                    Log::channel('pxsol')->warning("MANUAL: sin mapeo de habitación para room_id $roomId - reserva $bookingId");
                    $results['skipped'][] = ['booking_id' => $bookingId, 'reason' => "Sin mapeo para room_id $roomId"];
                    continue;
                }

                $response = $this->callApi('CancelaReservaPX', $params);

                Log::channel('pxsol')->info("MANUAL CancelaReservaPX $bookingId", [
                    'params'   => $params,
                    'response' => $response,
                ]);

                if (isset($response['RESULT']) && $response['RESULT'] === 'ERROR') {
                    $results['errors'][] = [
                        'booking_id' => $bookingId,
                        'hab'        => $internalHab,
                        'response'   => $response,
                    ];
                } else {
                    $results['processed'][] = [
                        'booking_id' => $bookingId,
                        'hab'        => $internalHab,
                        'response'   => $response,
                    ];
                }
            }
        }

        $results['total_processed'] = count($results['processed']);
        $results['total_errors']    = count($results['errors']);
        $results['total_skipped']   = count($results['skipped']);

        Log::channel('pxsol')->info("MANUAL processCancellations finalizado", $results);

        return response()->json($results);
    }

    private function fetchBookings(string $updatedFrom, string $updatedTo): array
    {
        $allBookings = [];
        $page = 1;

        do {
            $response = Http::withToken(config('services.pxsol_api_key_token'))
                ->get('https://gateway-prod.pxsol.com/v2/booking/list', [
                    'current_page' => $page,
                    'updated_from' => $updatedFrom,
                    'updated_to'   => $updatedTo,
                ]);

            if (!$response->successful()) {
                Log::channel('pxsol')->error("MANUAL: error al obtener página $page", ['body' => $response->body()]);
                break;
            }

            $data  = $response[0];
            $items = $data['data'] ?? [];
            $meta  = $data['meta'] ?? [];

            $allBookings = array_merge($allBookings, $items);
            $page++;
        } while (($meta['current_page'] ?? 1) < ($meta['last_page'] ?? 1));

        return $allBookings;
    }

    private function fetchBookingDetail(string $bookingId): ?array
    {
        $response = Http::withToken(config('services.pxsol_api_key_token'))
            ->get('https://gateway-prod.pxsol.com/v2/booking/info', ['id' => $bookingId]);

        return $response->successful() ? ($response->json()['data'] ?? null) : null;
    }

    private function callApi(string $endpoint, array $params): array
    {
        $environment = config('app.environment');
        $url = $environment === 'DEV'
            ? 'https://apieh.ehboutiqueexperience.com:9096'
            : 'https://apieh.ehboutiqueexperience.com:8086';

        // Convertir nulls a string vacío
        array_walk_recursive($params, function (&$v) {
            if (is_null($v) || $v === '') $v = '';
        });

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "$url/$endpoint");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return ['RESULT' => 'ERROR', 'MESSAGE' => $error];
        }

        curl_close($ch);
        return json_decode($response, true) ?? ['RESULT' => 'ERROR', 'MESSAGE' => 'Invalid JSON response'];
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class InternalApiController extends Controller
{
    public function get_url()
    {
        $environment = config("app.environment");
        return $environment === "DEV" ? "https://apieh.ehboutiqueexperience.com:8086" : "https://apieh.ehboutiqueexperience.com:8086";
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
                    return response()->json(['error' => $error_msg], 500);
                }

                curl_close($ch);

                return json_decode($response, true);
            } else {
                $response = $client->get("/$endpoint", [
                    'query' => $params // Enviamos los parámetros en formato GET
                ]);
            }

            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody()->getContents());
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


    // Métodos con nombres iguales a los endpoints
    public function Naciones()
    {
        return $this->fetchDataFromApi('Naciones');
    }

    // public function Naciones()
    // {
    //     $response = Http::get("https://apieh.ehboutiqueexperience.com:8087/Naciones");   
    //     if ($response->successful()) {
    //         return $response->json();
    //     } else {
    //         return $response->throw();
    //     }
    // }

    public function Naciones2()
    {
        $response = Http::get("https://apieh.ehboutiqueexperience.com:8086/Naciones");   
        if ($response->successful()) {
            return $response->json();
        } else {
            return $response->throw();
        }
    }

    public function Tarifas()
    {
        return $this->fetchDataFromApi('Tarifas');
    }

    public function Calendario(Request $request)
    {
        return $this->fetchDataFromApi('Calendario', [
            'FECHAD' => $request->FECHAD,
            'FECHAH' => $request->FECHAH,
            'HAB' => $request->HAB,
        ]);
    }

    public function Disponibilidad(Request $request)
    {
        return $this->fetchDataFromApi('Disponibilidad', [
            'FECHAD' => $request->FECHAD,
            'FECHAH' => $request->FECHAH,
        ]);
    }

    public function ReservaxCodigo(Request $request)
    {
        return $this->fetchDataFromApi('ReservaxCodigo', ['RSV' => $request->RSV]);
    }

    public function PedidoxCodigo(Request $request)
    {
        return $this->fetchDataFromApi('PedidoxCodigo', ['PED' => $request->PED]);
    }

    public function Articulos(Request $request)
    {
        return $this->fetchDataFromApi('Articulos', [
            'SECTOR' => $request->SECTOR,
            'BUSQUEDA' => $request->BUSQUEDA,
        ]);
    }

    public function Articulo(Request $request)
    {
        return $this->fetchDataFromApi('Articulo', [
            'ID' => $request->ID,
            'VAR1' => $request->VAR1 ?? 0,
            'VAR2' => $request->VAR2 ?? 0,
        ]);
    }

    public function Rubros(Request $request)
    {
        return $this->fetchDataFromApi('Rubros', ['SECTOR' => $request->SECTOR]);
    }

    public function ArticulosDestacados(Request $request)
    {
        return $this->fetchDataFromApi('ArticulosDestacados', ['SECTOR' => $request->SECTOR]);
    }

    public function TiposDocumentos()
    {
        return $this->fetchDataFromApi('TiposDocumentos');
    }

    public function Pedidos(Request $request)
    {
        return $this->fetchDataFromApi('Pedidos', $request->all());
    }

    public function Habitaciones()
    {
        return $this->fetchDataFromApi('Habitaciones');
    }

    public function Reservas(Request $request)
    {
        $fechah = $request->FECHAH ?: now()->format('d/m/Y');
        $fechad = $request->FECHAD ?: now()->subDays(30)->format('d/m/Y');
    
        return $this->fetchDataFromApi('Reservas', [
            'FECHAD' => $fechad,
            'FECHAH' => $fechah,
        ]);
    }

    public function IniciaReserva(Request $request)
    {
        return $this->fetchDataFromApi('IniciaReserva', $request->all(), 'POST');
    }

    public function CancelaReserva(Request $request)
    {
        return $this->fetchDataFromApi('CancelaReserva', $request->all(), 'POST');
    }

    public function ConfirmaReserva(Request $request)
    {
        return $this->fetchDataFromApi('ConfirmaReserva', $request->all(), 'POST');
    }

    public function ConfirmaPasajeros(Request $request)
    {
        return $this->fetchDataFromApi('ConfirmaPasajeros', $request->all(), 'POST');
    }

    public function IniciaPedido(Request $request)
    {
        return $this->fetchDataFromApi('IniciaPedido', $request->all(), 'POST');
    }

    public function CancelaPedido(Request $request)
    {
        return $this->fetchDataFromApi('CancelaPedido', $request->all(), 'POST');
    }

    public function ConfirmaPedido(Request $request)
    {
        return $this->fetchDataFromApi('ConfirmaPedido', $request->all(), 'POST');
    }

    public function RealizaCheck(Request $request)
    {
        return $this->fetchDataFromApi('RealizaCheck', $request->all(), 'POST');
    }
}
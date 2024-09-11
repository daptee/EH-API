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

    public function fetchDataFromApi($endpoint, $params = [], $method = 'GET')
    {
        try {
            $url = $this->get_url();
            $client = new \GuzzleHttp\Client(['base_uri' => $url, 'verify' => false]);

            if (strtoupper($method) === 'POST') {
                // $response = $client->post("/$endpoint", $params);
                $response = $client->post("/$endpoint", [
                    'form_params' => $params // Enviamos los parámetros en formato GET
                ]);
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

    public function Tarifas()
    {
        return $this->fetchDataFromApi('Tarifas');
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
        return $this->fetchDataFromApi('Pedidos', [
            'FECHAD' => $request->FECHAD,
            'FECHAH' => $request->FECHAH,
        ]);
    }

    public function Habitaciones()
    {
        return $this->fetchDataFromApi('Habitaciones');
    }

    public function Reservas(Request $request)
    {
        return $this->fetchDataFromApi('Reservas', [
            'FECHAD' => $request->FECHAD,
            'FECHAH' => $request->FECHAH,
        ]);
    }

    // public function IniciaReserva(Request $request)
    // {
    //     // dd($request->all());
    //     return $this->fetchDataFromApi('IniciaReserva', $request->all(), 'POST');
    // }

    public function IniciaReserva(Request $request)
    {
        $url = $this->get_url();
        $body_json = $request->all();
        $response = Http::post("$url/IniciaReserva", $body_json);   
        if ($response->successful()) {
            return $response->json();
        } else {
            return $response->throw();
        }
    }
}
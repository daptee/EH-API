<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class EhBoutiqueController extends Controller
{
    public function get_url()
    {
        $environment = config("app.environment");
        if($environment == "DEV"){
            $url = "https://apieh.ehboutiqueexperience.com:8086";
        }else{
            $url = "https://apieh.ehboutiqueexperience.com:8086"; // cambiar PROD
        }

        $client = new \GuzzleHttp\Client([
            'base_uri' => $url,
            'verify'   => false,
        ]);
    
        return $client;
    }
    
    // public function Naciones()
    // {
    //     $client = $this->get_url();
    //     $response = $client->get("/Naciones");   
       
    //     if ($response->getStatusCode() == 200) {
    //         return json_decode($response->getBody()->getContents());
    //     } else {
    //         return $response->getBody()->getContents();
    //     }
    // }

    // public function Naciones()
    // {
    //     // $url = $this->get_url();
    //     $response = Http::get("https://apieh.ehboutiqueexperience.com:8086/Naciones");   
    //     if ($response->successful()) {
    //         return $response->json();
    //     } else {
    //         return $response->throw();
    //     }
    // }

    public function Naciones()
    {
        try {
            $client = $this->get_url();
            $response = $client->get("/Naciones");
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody()->getContents());
            } else {
                return response()->json(['error' => 'Error en la solicitud a la API externa'], $response->getStatusCode());
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function Tarifas()
    {
        $client = $this->get_url();
        $response = $client->get("/Tarifas");   
    
        if ($response->getStatusCode() == 200) {
            return json_decode($response->getBody()->getContents());
        } else {
            return $response->getBody()->getContents();
        }
    }

    public function Disponibilidad(Request $request)
    {
        $client = $this->get_url();
        $FECHAD = $request->FECHAD; 
        $FECHAH = $request->FECHAH; 
        $response = $client->get("/Disponibilidad?FECHAD=$FECHAD&FECHAH=$FECHAH");  
    
        if ($response->getStatusCode() == 200) {
            return json_decode($response->getBody()->getContents());
        } else {
            return $response->getBody()->getContents();
        }
    }

    public function ReservaxCodigo(Request $request)
    {
        $client = $this->get_url();
        $RSV = $request->RSV; 
        $response = $client->get("/ReservaxCodigo?RSV=$RSV");  
    
        if ($response->getStatusCode() == 200) {
            return json_decode($response->getBody()->getContents());
        } else {
            return $response->getBody()->getContents();
        }
    }
    
    public function Articulos(Request $request)
    {
        $client = $this->get_url();
        $SECTOR = $request->SECTOR; 
        $response = $client->get("/Articulos?SECTOR=$SECTOR");  
    
        if ($response->getStatusCode() == 200) {
            return json_decode($response->getBody()->getContents());
        } else {
            return $response->getBody()->getContents();
        }
    }

    public function CancelaReserva(Request $request)
    {
        $client = $this->get_url();
        $body_json = $request->all();
        $response = $client->post("/CancelaReserva", $body_json);  

        if ($response->getStatusCode() == 200) {
            return json_decode($response->getBody()->getContents());
        } else {
            return $response->getBody()->getContents();
        }
    }

    public function IniciaReserva(Request $request)
    {
        $client = $this->get_url();
        $body_json = $request->all();
        $response = $client->post("/IniciaReserva", $body_json);  

        if ($response->getStatusCode() == 200) {
            return json_decode($response->getBody()->getContents());
        } else {
            return $response->getBody()->getContents();
        }
    }

    public function ConfirmaReserva(Request $request)
    {
        $client = $this->get_url();
        $body_json = $request->all();
        $response = $client->post("/ConfirmaReserva", $body_json);  

        if ($response->getStatusCode() == 200) {
            return json_decode($response->getBody()->getContents());
        } else {
            return $response->getBody()->getContents();
        }
    }

    public function ConfirmaPasajeros(Request $request)
    {
        $client = $this->get_url();
        $body_json = $request->all();
        $response = $client->post("/ConfirmaPasajeros", $body_json);  

        if ($response->getStatusCode() == 200) {
            return json_decode($response->getBody()->getContents());
        } else {
            return $response->getBody()->getContents();
        }
    }
}

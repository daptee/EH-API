<?php

namespace App\Http\Controllers;

use App\Mail\FormContact;
use App\Mail\MatrizDesign;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class FormController extends Controller
{
    public function form_contact(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'message' => 'required'
        ]);

        $data = $request->all();

        try {
            $mail_to = config('services.mail_to_contact');
            Mail::to($mail_to)->send(new FormContact($data));
        } catch (Exception $error) {
            Log::debug(print_r(["message" => $error->getMessage() . " error en envio de mail de contacto", $error->getLine()],  true));
            return response()->json(["message" => "Error en envio de mail contacto: " . $error->getMessage()]);
        }

        return response()->json(['message' => 'Contacto enviado con exito.']);
    }

    public function matriz_design(Request $request)
    {
        $data = $request->all();

        try {
            $mail_to = config('services.mail_to_contact');
            Mail::to($mail_to)->send(new MatrizDesign($data));
            // Mail::to("slarramendy@daptee.com.ar")->send(new MatrizDesign($data));
        } catch (Exception $error) {
            Log::debug(print_r(["message" => $error->getMessage() . " error en envio de mail matriz design", $error->getLine()],  true));
            return response()->json(["message" => "Error en envio de mail matriz design: " . $error->getMessage()]);
        }

        return response()->json(['message' => 'Mail matriz-design enviado con exito.']);
    }
}

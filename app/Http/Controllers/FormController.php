<?php

namespace App\Http\Controllers;

use App\Mail\ExcursionForm;
use App\Mail\TransferForm;
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
            return response()->json(["message" => "Error en envio de mail contacto: " . $error->getMessage()], 400);
        }

        return response()->json(['message' => 'Contacto enviado con exito.']);
    }

    public function excursion_form(Request $request)
    {
        $request->validate([
            'name'          => 'required',
            'lastname'      => 'required',
            'email'         => 'required|email',
            'phone'         => 'required',
            'message'       => 'required',
            'excursion_name' => 'required',
        ]);

        $data = $request->all();

        try {
            $mail_to = array_map('trim', explode(',', config('services.mail_to_excursions_form')));
            Mail::to($mail_to)->send(new ExcursionForm($data));
        } catch (Exception $error) {
            Log::debug(print_r(["message" => $error->getMessage() . " error en envio de mail excursion form", $error->getLine()], true));
            return response()->json(["message" => "Error en envio de mail: " . $error->getMessage()], 400);
        }

        return response()->json(['message' => 'Solicitud enviada con éxito.']);
    }

    public function transfer_form(Request $request)
    {
        $request->validate([
            'name'          => 'required',
            'lastname'      => 'required',
            'email'         => 'required|email',
            'phone'         => 'required',
            'message'       => 'required',
            'transfer_name' => 'required',
        ]);

        $data = $request->all();

        try {
            $mail_to = array_map('trim', explode(',', config('services.mail_to_excursions_form')));
            Mail::to($mail_to)->send(new TransferForm($data));
        } catch (Exception $error) {
            Log::debug(print_r(["message" => $error->getMessage() . " error en envio de mail transfer form", $error->getLine()], true));
            return response()->json(["message" => "Error en envio de mail: " . $error->getMessage()], 400);
        }

        return response()->json(['message' => 'Solicitud enviada con éxito.']);
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
            return response()->json(["message" => "Error en envio de mail matriz design: " . $error->getMessage()], 400);
        }

        return response()->json(['message' => 'Mail matriz-design enviado con exito.']);
    }
}

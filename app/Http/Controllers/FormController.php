<?php

namespace App\Http\Controllers;

use App\Mail\FormContact;
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
            Mail::to("slarramendy@daptee.com.ar")->send(new FormContact($data));                        
        } catch (Exception $error) {
            Log::debug(print_r(["message" => $error->getMessage() . " error en envio de mail de contacto", $error->getLine()],  true));
        }

        return response()->json(['message' => 'Contacto enviado con exito.']);
    }
}

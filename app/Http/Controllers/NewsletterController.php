<?php

namespace App\Http\Controllers;

use App\Models\Newsletter;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public $model = Newsletter::class;

    public function newsletter_register_email(Request $request)
    {
        $request->validate([
            'email' => 'required'
        ]);

        $newsletter_exist = $this->model::where('email', $request->email)->first();

        if($newsletter_exist)
            return response()->json(['message' => 'Registro existente.'], 402);

        $newsletter = new Newsletter();
        $newsletter->email = $request->email;
        $newsletter->save();

        return response()->json([
            'message' => 'Email registrado con exito.',
            'newsletter' => $newsletter
        ]);
    }
}

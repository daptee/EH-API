<?php

namespace App\Http\Controllers;

use App\Mail\recoverPasswordMailable;
use App\Mail\SendCodeMail;
use App\Models\Locality;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{

    public $model = User::class;
    public $s = "usuario";
    public $sp = "usuarios";
    public $ss = "usuario/s";
    public $v = "o"; 
    public $pr = "el"; 
    public $prp = "los";

    public function recover_password_user(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);
        
        $user = User::where('email', $request->email)->first();

        if(!$user)
            return response()->json(['message' => 'No existe un usuario con el mail solicitado.'], 402);
        
        try {
            $new_password = Str::random(16);
            $user->password = Hash::make($new_password);
            $user->save();
            
            $data = [
                'name' => $user->nombre,
                'email' => $user->email,
                'password' => $new_password,
            ];
            Mail::to($user->email)->send(new recoverPasswordMailable($data));
        } catch (Exception $error) {
            return response(["error" => $error->getMessage()], 500);
        }
       
        return response()->json(['message' => 'Correo enviado con exito.'], 200);
        
    }

    public function show($id)
    {
        $user = User::find($id);
        if(!$user)
            return response()->json(['message' => 'Usuario no encontrado, por favor verifica el ID solicitado'], 400);

        return response()->json([
            'user' => $this->model::getAllDataUser($id)
        ]);
    }

    public function update(Request $request)
    {
        if (Auth::check()) {

            $request->validate([
                'email' => 'unique:users,email,' . Auth::user()->id
            ]);

            if(!Locality::find($request->locality_id))
                return response()->json(['message' => 'Localidad no encontrada, por favor verifica el valor en locality_id.'], 400);

            $user = Auth::user();
        
            $user->name = $request->name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->locality_id = $request->locality_id;
            
            if(isset($request->password))
                $user->password = Hash::make($request->password);
    
            $user->save();
        }else{
            return response()->json(['message' => 'Usuario no autenticado.'], 400);
        }

        return response()->json([
            'message' => 'Usuario actualizado con exito.',
            'user' => $this->model::getAllDataUser($user->id)
        ]);
    }

    public function update_profile_picture(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|file|max:2048',
        ]);

        $user = Auth::user();
        
        if($user->profile_picture){
            $file_path = public_path($user->profile_picture);
        
            if (file_exists($file_path))
                 unlink($file_path);
        }

        $path = $this->save_image_public_folder($request->profile_picture, "users/profiles/", null);
        
        $user->profile_picture = $path;
        $user->save();

        $message = "Usuario actualizado exitosamente";

        return response(compact("message", "user"));
    }

    public function save_image_public_folder($file, $path_to_save, $variable_id)
    {
        $fileName = Str::random(5) . time() . '.' . $file->extension();
                        
        if($variable_id){
            $file->move(public_path($path_to_save . $variable_id), $fileName);
            $path = "/" . $path_to_save . $variable_id . "/$fileName";
        }else{
            $file->move(public_path($path_to_save), $fileName);
            $path = "/" . $path_to_save . $fileName;
        }
        

        return $path;
    }

    public function send_code_email(Request $request)
    {
        $request->validate([
            'reservation_number' => 'required',
            'contact_email' => 'required',
            'name' => 'required',
            'room_number' => 'required',
            'code' => 'required',
        ]);
        try {
            $data = $request->all();

            Mail::to($request->contact_email)->send(new SendCodeMail($data));
        } catch (Exception $error) {
            return response(["error" => $error->getMessage()], 500);
        }
       
        return response()->json(['message' => 'Correo enviado con exito.'], 200);
    }
}

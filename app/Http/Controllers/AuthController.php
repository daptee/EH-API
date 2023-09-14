<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Auth;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\User;
use App\Models\UserType;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public $model = User::class;
    public $s = "usuario";
    public $sp = "usuarios";
    public $ss = "usuario/s";
    public $v = "o"; 
    public $pr = "el"; 
    public $prp = "los";
    
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        try{
            $user = User::where('email' , $credentials['email'])->get();

            if($user->count() == 0)
                return response()->json(['message' => 'Usuario y/o clave no válidos.'], 400);

            if (! $token = JWTAuth::attempt($credentials))
                return response()->json(['message' => 'Usuario y/o clave no válidos.'], 400);

        }catch (JWTException $e) {
            return response()->json(['message' => 'No fue posible crear el Token de Autenticación '], 500);
        }
    
        // Session::put('applocale', $request);
        return $this->respondWithToken($token, Auth::user()->id);
    }

    public function login_admin(LoginRequest $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // User admin 
        $user_to_validate = User::where('email', $request->email)->first();
        
        if(!isset($user_to_validate) || $user_to_validate->user_type_id != UserType::ADMIN)
            return response()->json(['message' => 'Email y/o clave no válidos.'], 400);
        
        $credentials = $request->only('email', 'password');

        if (! $token = JWTAuth::attempt($credentials))
            return response()->json(['message' => 'Email y/o clave no válidos.'], 400);

        return $this->respondWithToken($token, Auth::user()->id);
    }

    public function register(RegisterRequest $request)
    {
        $message = "Error al crear {$this->s} en registro";
        $data = $request->all();

        $new = new $this->model($data);
        try {
            $new->password = Hash::make($data['password']);
            $new->save();
            // $data = $this->model::with($this->model::SHOW)->findOrFail($new->id);
            $data = $new;
        } catch (ModelNotFoundException $error) {
            return response(["message" => "No se encontro {$this->s}", "error" => $error->getMessage()], 404);
        } catch (Exception $error) {
            return response(["message" => "Error al recuperar {$this->s}", "error" => $error->getMessage()], 500);
        }
        $message = "Registro de {$this->s} exitoso";
        return response(compact("message", "data"));
    }

    public function logout(){
        try{
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json(['message' => 'Logout exitoso.']);
        }catch (JWTException $e) {

            return response()->json(['message' => $e->getMessage()])->setstatusCode(500);
        }catch(Exception $e) {

            return response()->json(['message' => $e->getMessage()])->setstatusCode(500);
        }
    }

    protected function respondWithToken($token,$id){
        $expire_in = config('jwt.ttl');
        $data = [ 'user' => User::getAllDataUser($id) ];

        return response()->json([
            'message' => 'Login exitoso.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $expire_in * 60,
            'data' => $data
        ]);
    }

}

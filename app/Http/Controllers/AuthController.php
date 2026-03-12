<?php

namespace App\Http\Controllers;

use App\Helpers\SecurityLogger;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Mail\AdminOtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
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
    
    // public function login(LoginRequest $request)
    // {
    //     $credentials = $request->only('email', 'password');
    //     try{
    //         $user = User::where('email' , $credentials['email'])->get();

    //         if($user->count() == 0)
    //             return response()->json(['message' => 'Usuario y/o clave no válidos.'], 400);

    //         if (! $token = JWTAuth::attempt($credentials))
    //             return response()->json(['message' => 'Usuario y/o clave no válidos.'], 400);

    //     }catch (JWTException $e) {
    //         return response()->json(['message' => 'No fue posible crear el Token de Autenticación '], 500);
    //     }
    
    //     // Session::put('applocale', $request);
    //     return $this->respondWithToken($token, Auth::user()->id);
    // }

    public function login_super_admin(LoginRequest $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $ip = $request->ip();
        $ua = $request->header('User-Agent', '-');

        // 1. Verificar que el usuario exista y sea super admin
        $user = User::where('email', $request->email)->first();

        if (!$user || $user->user_type_id != UserType::SUPERADMIN) {
            SecurityLogger::failedLogin($request->email, $ip, $ua, 'user_not_found_or_not_superadmin');
            return response()->json(['message' => 'Email y/o clave no válidos.'], 400);
        }

        // 2. Verificar si la contraseña fue expirada (reset masivo)
        if ($user->password_expired) {
            SecurityLogger::failedLogin($request->email, $ip, $ua, 'password_expired');
            return response()->json([
                'message'          => 'Tu contraseña ha expirado. Usá el flujo de recuperación de contraseña.',
                'password_expired' => true,
            ], 400);
        }

        // 3. Validar credenciales
        $credentials = $request->only('email', 'password');

        if (!JWTAuth::attempt($credentials)) {
            SecurityLogger::failedLogin($request->email, $ip, $ua, 'wrong_credentials');
            return response()->json(['message' => 'Email y/o clave no válidos.'], 400);
        }

        // 4. Credenciales OK — generar OTP y enviarlo por email (NO emitir JWT todavía)
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->otp_code       = $otp;
        $user->otp_expires_at = now()->addMinutes(10);
        $user->save();

        Mail::to($user->email)->send(new AdminOtpMail($otp, $user->name));

        SecurityLogger::adminAction('2fa_otp_sent', $ip, $user->email);

        return response()->json(['pending_2fa' => true], 200);
    }

    public function verify_otp_super_admin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|string|size:6',
        ]);

        $ip = $request->ip();
        $ua = $request->header('User-Agent', '-');

        $user = User::where('email', $request->email)
                    ->where('user_type_id', UserType::SUPERADMIN)
                    ->first();

        // 1. Verificar que existe OTP pendiente
        if (!$user || !$user->otp_code || !$user->otp_expires_at) {
            return response()->json(['message' => 'Código inválido o expirado.'], 400);
        }

        // 2. Verificar expiración
        if ($user->otp_expires_at->lt(now())) {
            $user->otp_code       = null;
            $user->otp_expires_at = null;
            $user->save();
            return response()->json(['message' => 'El código ha expirado.'], 400);
        }

        // 3. Verificar código (resistente a timing attacks)
        if (!hash_equals($user->otp_code, $request->otp)) {
            SecurityLogger::failedLogin($request->email, $ip, $ua, 'wrong_otp');
            return response()->json(['message' => 'Código inválido.'], 400);
        }

        // 4. OTP correcto — limpiar y emitir JWT
        $user->otp_code       = null;
        $user->otp_expires_at = null;
        $user->save();

        $token = JWTAuth::fromUser($user);

        SecurityLogger::adminAction('login_success_2fa', $ip, $user->email);

        return $this->respondWithToken($token, $user->id);
    }

    // public function register(RegisterRequest $request)
    // {
    //     $message = "Error al crear {$this->s} en registro";
    //     $data = $request->all();

    //     $new = new $this->model($data);
    //     try {
    //         $new->password = Hash::make($data['password']);
    //         $new->save();
    //         // $data = $this->model::with($this->model::SHOW)->findOrFail($new->id);
    //         $data = $new;
    //     } catch (ModelNotFoundException $error) {
    //         return response(["message" => "No se encontro {$this->s}", "error" => $error->getMessage()], 404);
    //     } catch (Exception $error) {
    //         return response(["message" => "Error al recuperar {$this->s}", "error" => $error->getMessage()], 500);
    //     }
    //     $message = "Registro de {$this->s} exitoso";
    //     return response(compact("message", "data"));
    // }

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

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\AgencyUser;
use Exception;

class AgencyAuthController extends Controller
{
    public $model = AgencyUser::class;
    public $s = "usuario de agencia";

    /**
     * Registro de nuevo usuario
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name'  => 'required|string|max:255',
            'last_name'   => 'required|string|max:255',
            'email'       => 'required|email|unique:agency_users,email',
            'password'    => 'required|string|min:6',
            'agency_code' => 'required|string',
        ], [
            'email.unique' => 'El correo electrónico ya está registrado.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = AgencyUser::create([
                'first_name'  => $request->first_name,
                'last_name'   => $request->last_name,
                'email'       => $request->email,
                'password'    => $request->password, // se hashea automáticamente
                'agency_code' => $request->agency_code,
            ]);

            return response()->json([
                'message' => 'Registro de usuario de agencia exitoso.',
                'data' => $user,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al registrar el usuario.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login JWT
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $credentials = $request->only('email', 'password');

        try {
            if (!$token = Auth::guard('agency')->attempt($credentials)) {
                return response()->json(['message' => 'Email y/o clave no válidos.'], 400);
            }
        } catch (JWTException $e) {
            return response()->json(['message' => 'No fue posible crear el Token de Autenticación.'], 500);
        }

        return $this->respondWithToken($token, Auth::guard('agency')->user()->id);
    }

    /**
     * Logout
     */
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'Logout exitoso.']);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Error al invalidar el token.'], 500);
        }
    }

    protected function respondWithToken($token, $id)
    {
        $expire_in = config('jwt.ttl');

        $user = AgencyUser::find($id);

        return response()->json([
            'message' => 'Login exitoso.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $expire_in * 60,
            'data' => [
                'user' => $user,
            ],
        ]);
    }
}
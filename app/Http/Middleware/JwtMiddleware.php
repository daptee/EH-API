<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;


class JwtMiddleware extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Intentar con el request normal (Authorization header estándar)
            $user = JWTAuth::setRequest($request)->parseToken()->authenticate();

            if (!$user) {
                return response()->json(['status' => 'Authorization Token not found'], 401);
            }
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['status' => 'Token is Invalid'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['status' => 'Token is Expired'], 401);
        } catch (Exception $e) {
            // Authorization header no encontrado — intentar fallbacks
            $token = $this->extractTokenFromFallbacks($request);

            if (!$token) {
                return response()->json(['status' => 'Authorization Token not found'], 401);
            }

            try {
                // Setear el token directamente en JWTAuth, sin depender de headers
                $user = JWTAuth::setToken($token)->authenticate();

                if (!$user) {
                    return response()->json(['status' => 'Authorization Token not found'], 401);
                }
            } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
                return response()->json(['status' => 'Token is Invalid'], 401);
            } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
                return response()->json(['status' => 'Token is Expired'], 401);
            } catch (Exception $e) {
                return response()->json(['status' => 'Authorization Token not found'], 401);
            }
        }

        return $next($request);
    }

    private function extractTokenFromFallbacks(Request $request): ?string
    {
        $auth = null;

        // Fallback 1: apache_request_headers() — por si el proxy lo pasa de otra forma
        if (function_exists('apache_request_headers')) {
            $apacheHeaders = apache_request_headers();
            $auth = $apacheHeaders['Authorization'] ?? $apacheHeaders['authorization'] ?? null;
        }

        // Fallback 2: header X-Authorization
        if (!$auth) {
            $auth = $request->header('X-Authorization');
        }

        if (!$auth) {
            return null;
        }

        // Quitar el prefijo "Bearer " si viene incluido
        return preg_replace('/^Bearer\s+/i', '', trim($auth));
    }
}

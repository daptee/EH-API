<?php

namespace App\Http\Middleware;

use App\Helpers\SecurityLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccessAuditLog
{
    /**
     * Rutas consideradas sensibles: se loguean también en sensitive-requests-YYYY-MM.log.
     * Se comparan como prefijo o coincidencia exacta del path (sin /api/).
     */
    private const SENSITIVE_PATHS = [
        'login_super_admin',
        'logout',
        'recover-password',
        'agency/login',
        'agency/logout',
        'agency/recover-password',
        'agency/profile/update',
        'room/images',
        'clear-cache',
        'test-mail',
        'admin/emergency-reset-passwords',
    ];

    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    public function terminate(Request $request, $response): void
    {
        try {
            $user = null;
            if (Auth::check()) {
                $authUser = Auth::user();
                $user = ($authUser->email ?? null) . ' (id:' . ($authUser->id ?? '?') . ')';
            }

            $method = $request->method();
            $path   = $request->path();
            $status = $response->getStatusCode();
            $ip     = $request->ip();
            $ua     = $request->header('User-Agent', '-');

            // Log general de acceso autenticado
            $dir  = storage_path('logs/security');
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $line = implode(' | ', [
                now()->toDateTimeString(),
                $method,
                $path,
                "status:{$status}",
                "ip:{$ip}",
                "user:" . ($user ?? 'anonymous'),
                "ua:" . substr($ua, 0, 100),
            ]);
            file_put_contents($dir . '/access-audit-' . now()->format('Y-m') . '.log', $line . PHP_EOL, FILE_APPEND | LOCK_EX);

            // Log adicional si la ruta es sensible
            if ($this->isSensitivePath($path)) {
                SecurityLogger::sensitiveRequest($method, $path, $status, $ip, $user, $ua);
            }
        } catch (\Throwable $e) {
            // El log nunca debe romper la respuesta
        }
    }

    private function isSensitivePath(string $path): bool
    {
        // Normalizar quitando prefijo api/
        $normalized = ltrim(preg_replace('#^api/#', '', ltrim($path, '/')), '/');

        foreach (self::SENSITIVE_PATHS as $sensitive) {
            if ($normalized === $sensitive || str_starts_with($normalized, $sensitive)) {
                return true;
            }
        }
        return false;
    }
}

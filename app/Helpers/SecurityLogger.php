<?php

namespace App\Helpers;

class SecurityLogger
{
    /**
     * Registra un intento de login fallido.
     */
    public static function failedLogin(string $email, string $ip, string $userAgent, string $reason = ''): void
    {
        $line = implode(' | ', [
            now()->toDateTimeString(),
            'FAILED_LOGIN',
            "email:{$email}",
            "ip:{$ip}",
            "reason:{$reason}",
            "ua:" . substr($userAgent, 0, 120),
        ]);
        self::write('failed-logins', $line);
    }

    /**
     * Registra operaciones sobre endpoints sensibles.
     */
    public static function sensitiveRequest(
        string $method,
        string $path,
        int $status,
        string $ip,
        ?string $user,
        string $userAgent
    ): void {
        $line = implode(' | ', [
            now()->toDateTimeString(),
            strtoupper($method),
            $path,
            "status:{$status}",
            "ip:{$ip}",
            "user:" . ($user ?? 'anonymous'),
            "ua:" . substr($userAgent, 0, 120),
        ]);
        self::write('sensitive-requests', $line);
    }

    /**
     * Registra actividad de bots / scanners detectados.
     */
    public static function botDetection(string $reason, string $ip, string $userAgent, string $path): void
    {
        $line = implode(' | ', [
            now()->toDateTimeString(),
            'BOT_BLOCKED',
            "reason:{$reason}",
            "ip:{$ip}",
            "path:{$path}",
            "ua:" . substr($userAgent, 0, 200),
        ]);
        self::write('bot-detection', $line);
    }

    /**
     * Registra cuándo se alcanza el rate limit.
     */
    public static function rateLimit(string $limiter, string $email, string $ip, string $path): void
    {
        $line = implode(' | ', [
            now()->toDateTimeString(),
            'RATE_LIMIT',
            "limiter:{$limiter}",
            "email:{$email}",
            "ip:{$ip}",
            "path:{$path}",
        ]);
        // Va al archivo de failed-logins si es el limiter de login
        $file = ($limiter === 'login') ? 'failed-logins' : 'sensitive-requests';
        self::write($file, $line);
    }

    /**
     * Registra operaciones de administración críticas (ej: emergency reset).
     */
    public static function adminAction(string $action, string $ip, string $user, string $detail = ''): void
    {
        $line = implode(' | ', [
            now()->toDateTimeString(),
            'ADMIN_ACTION',
            "action:{$action}",
            "user:{$user}",
            "ip:{$ip}",
            "detail:{$detail}",
        ]);
        self::write('sensitive-requests', $line);
    }

    private static function write(string $type, string $line): void
    {
        try {
            $dir = storage_path('logs/security');
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $file = $dir . "/{$type}-" . now()->format('Y-m') . '.log';
            file_put_contents($file, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
        } catch (\Throwable $e) {
            // El log nunca debe romper la aplicación
        }
    }
}

<?php

namespace App\Http\Middleware;

use App\Helpers\SecurityLogger;
use Closure;
use Illuminate\Http\Request;

class BotDetection
{
    /**
     * Patrones de User-Agent de herramientas de ataque / scanners.
     * Comparación case-insensitive.
     */
    private const MALICIOUS_UA_PATTERNS = [
        'sqlmap',
        'nikto',
        'nmap',
        'masscan',
        'zgrab',
        'dirbuster',
        'gobuster',
        'wfuzz',
        'burpsuite',
        'burp suite',
        'hydra',
        'medusa',
        'nessus',
        'openvas',
        'acunetix',
        'appscan',
        'webinspect',
        'nuclei',
        'metasploit',
        'whatweb',
        'w3af',
        'skipfish',
        'arachni',
        'zap',                // OWASP ZAP
        'havij',
        'pangolin',
        'netsparker',
        'vega',
        'webscarab',
        'paros',
    ];

    public function handle(Request $request, Closure $next)
    {
        $ua   = $request->header('User-Agent', '');
        $ip   = $request->ip();
        $path = $request->path();

        // 1. Bloquear si no hay User-Agent
        if (empty(trim($ua))) {
            SecurityLogger::botDetection('empty_user_agent', $ip, '', $path);
            return response()->json(['message' => 'Acceso denegado.'], 403);
        }

        // 2. Bloquear si el UA coincide con herramientas de ataque conocidas
        $uaLower = strtolower($ua);
        foreach (self::MALICIOUS_UA_PATTERNS as $pattern) {
            if (str_contains($uaLower, $pattern)) {
                SecurityLogger::botDetection("malicious_ua:{$pattern}", $ip, $ua, $path);
                return response()->json(['message' => 'Acceso denegado.'], 403);
            }
        }

        return $next($request);
    }
}

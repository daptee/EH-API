<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequestSizeLimit
{
    public function handle(Request $request, Closure $next)
    {
        $contentLength = $request->server('CONTENT_LENGTH');

        if ($contentLength) {
            $isMultipart = str_contains($request->header('Content-Type', ''), 'multipart');
            $limit = $isMultipart ? (10 * 1024 * 1024) : (1 * 1024 * 1024); // 10MB multipart, 1MB JSON

            if ((int) $contentLength > $limit) {
                return response()->json([
                    'message' => 'El tamaño del request supera el límite permitido.',
                ], 413);
            }
        }

        return $next($request);
    }
}

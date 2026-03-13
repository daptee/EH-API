<?php
// ELIMINAR ESTE ARCHIVO DESPUÉS DE USAR
define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

Illuminate\Support\Facades\Artisan::call('config:clear');
Illuminate\Support\Facades\Artisan::call('route:clear');
Illuminate\Support\Facades\Artisan::call('cache:clear');
Illuminate\Support\Facades\Artisan::call('optimize');

echo json_encode(['message' => 'Cache cleared. Eliminá este archivo del servidor.']);

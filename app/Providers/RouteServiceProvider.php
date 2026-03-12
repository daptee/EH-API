<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('login', function (Request $request) {
            // Clave por email (no por IP) para evitar evasión via rotación de IPs
            $key = strtolower($request->input('email', '')) ?: $request->ip();
            return Limit::perMinute(10)->by($key)->response(function () use ($request) {
                $this->logRateLimited('login', $request);
                return response()->json(['message' => 'Demasiados intentos. Intente nuevamente en unos minutos.'], 429);
            });
        });

        // Endpoints que envían emails (recover-password, send/code/email)
        // 5 req/min por IP para evitar abuso de envío masivo
        RateLimiter::for('mail_send', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip())->response(function () use ($request) {
                $this->logRateLimited('mail_send', $request);
                return response()->json(['message' => 'Demasiadas solicitudes. Intente nuevamente en unos minutos.'], 429);
            });
        });
    }

    private function logRateLimited(string $limiter, Request $request): void
    {
        $email = $request->input('email', 'N/A');
        $ip    = $request->ip();
        $path  = $request->path();

        \App\Helpers\SecurityLogger::rateLimit($limiter, $email, $ip, $path);

        $alertEmails = array_filter(array_map(
            'trim',
            explode(',', env('SECURITY_ALERT_EMAILS', ''))
        ));

        if (!empty($alertEmails)) {
            $subject = '[EH API] Rate limit alcanzado — posible ataque de fuerza bruta';
            $body    = "Se alcanzó el rate limit en [{$limiter}].\n\nEmail: {$email}\nIP: {$ip}\nEndpoint: {$path}\nFecha: " . now()->toDateTimeString();

            foreach ($alertEmails as $alertEmail) {
                \Illuminate\Support\Facades\Mail::raw($body, function ($message) use ($alertEmail, $subject) {
                    $message->to($alertEmail)->subject($subject);
                });
            }
        }
    }
}

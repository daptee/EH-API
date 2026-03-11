<?php

namespace Tests\Feature\Security;

use App\Models\User;
use App\Models\UserType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    private const VALID_UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36';

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('login');
        RateLimiter::clear('mail_send');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeSuperAdmin(array $overrides = []): User
    {
        return User::create(array_merge([
            'name'             => 'Super Admin',
            'last_name'        => 'Test',
            'email'            => 'superadmin@test.com',
            'password'         => Hash::make('password123'),
            'user_type_id'     => UserType::SUPERADMIN,
            'password_expired' => false,
        ], $overrides));
    }

    private function tokenFor(User $user): string
    {
        return JWTAuth::fromUser($user);
    }

    // =========================================================================
    // BOT DETECTION
    // =========================================================================

    /** @test */
    public function it_blocks_request_with_empty_user_agent(): void
    {
        $response = $this->postJson('/api/login_super_admin', [
            'email'    => 'test@test.com',
            'password' => 'secret',
        ], ['User-Agent' => '']);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_blocks_sqlmap_user_agent(): void
    {
        $response = $this->postJson('/api/login_super_admin', [], [
            'User-Agent' => 'sqlmap/1.7.8#stable (https://sqlmap.org)',
        ]);
        $response->assertStatus(403);
    }

    /** @test */
    public function it_blocks_nikto_user_agent(): void
    {
        $response = $this->postJson('/api/login_super_admin', [], [
            'User-Agent' => 'Mozilla/5.0 nikto/2.1.6',
        ]);
        $response->assertStatus(403);
    }

    /** @test */
    public function it_blocks_nuclei_user_agent(): void
    {
        $response = $this->getJson('/api/room/images', [
            'User-Agent' => 'Nuclei - Open-source project (github.com/projectdiscovery/nuclei)',
        ]);
        $response->assertStatus(403);
    }

    /** @test */
    public function it_allows_valid_browser_user_agent(): void
    {
        // Con UA válido no debe devolver 403, puede ser cualquier otro status
        $response = $this->postJson('/api/login_super_admin', [
            'email'    => 'notexists@test.com',
            'password' => 'wrong',
        ], ['User-Agent' => self::VALID_UA]);

        $response->assertStatus(400);
    }

    // =========================================================================
    // RUTAS PROTEGIDAS — SIN TOKEN
    // =========================================================================

    /** @test */
    public function it_rejects_logout_without_token(): void
    {
        $this->postJson('/api/logout', [], ['User-Agent' => self::VALID_UA])
             ->assertStatus(401);
    }

    /** @test */
    public function it_rejects_room_images_post_without_token(): void
    {
        $this->postJson('/api/room/images', [], ['User-Agent' => self::VALID_UA])
             ->assertStatus(401);
    }

    /** @test */
    public function it_rejects_room_images_delete_without_token(): void
    {
        $this->postJson('/api/room/images/delete/1', [], ['User-Agent' => self::VALID_UA])
             ->assertStatus(401);
    }

    /** @test */
    public function it_rejects_clear_cache_without_token(): void
    {
        $this->getJson('/api/clear-cache', ['User-Agent' => self::VALID_UA])
             ->assertStatus(401);
    }

    /** @test */
    public function it_rejects_emergency_reset_without_token(): void
    {
        $this->postJson('/api/admin/emergency-reset-passwords', [
            'confirm'        => true,
            'admin_password' => 'anything',
        ], ['User-Agent' => self::VALID_UA])->assertStatus(401);
    }

    // =========================================================================
    // INTERNAL-API-EH — REQUIEREN TOKEN
    // =========================================================================

    /** @test */
    public function it_rejects_internal_api_articulos_without_token(): void
    {
        $this->getJson('/api/internal-api-eh/Articulos', ['User-Agent' => self::VALID_UA])
             ->assertStatus(401);
    }

    /** @test */
    public function it_rejects_internal_api_rubros_without_token(): void
    {
        $this->getJson('/api/internal-api-eh/Rubros', ['User-Agent' => self::VALID_UA])
             ->assertStatus(401);
    }

    /** @test */
    public function it_rejects_internal_api_pedidos_without_token(): void
    {
        $this->getJson('/api/internal-api-eh/Pedidos', ['User-Agent' => self::VALID_UA])
             ->assertStatus(401);
    }

    /** @test */
    public function it_rejects_internal_api_reservas_without_token(): void
    {
        $this->getJson('/api/internal-api-eh/Reservas', ['User-Agent' => self::VALID_UA])
             ->assertStatus(401);
    }

    /** @test */
    public function it_rejects_internal_api_calendario_without_token(): void
    {
        $this->getJson('/api/internal-api-eh/Calendario', ['User-Agent' => self::VALID_UA])
             ->assertStatus(401);
    }

    /** @test */
    public function it_rejects_internal_api_inicia_reserva_without_token(): void
    {
        $this->postJson('/api/internal-api-eh/IniciaReserva', [], ['User-Agent' => self::VALID_UA])
             ->assertStatus(401);
    }

    /** @test */
    public function it_rejects_internal_api_inicia_pedido_without_token(): void
    {
        $this->postJson('/api/internal-api-eh/IniciaPedido', [], ['User-Agent' => self::VALID_UA])
             ->assertStatus(401);
    }

    /** @test */
    public function it_passes_internal_api_auth_layer_with_valid_token(): void
    {
        $admin = $this->makeSuperAdmin();
        $token = $this->tokenFor($admin);

        // Con token válido el middleware pasa — puede llegar un error de conexión al PMS
        // externo (500/etc), pero lo importante es que NO devuelva 401.
        $response = $this->getJson('/api/internal-api-eh/Naciones', [
            'Authorization' => "Bearer {$token}",
            'User-Agent'    => self::VALID_UA,
        ]);

        $this->assertNotEquals(401, $response->status(), 'Con token válido no debe devolver 401');
    }

    // =========================================================================
    // TOKEN INVÁLIDO
    // =========================================================================

    /** @test */
    public function it_rejects_invalid_token_on_protected_route(): void
    {
        $this->postJson('/api/logout', [], [
            'Authorization' => 'Bearer token.invalido.xxx',
            'User-Agent'    => self::VALID_UA,
        ])->assertStatus(401);
    }

    // =========================================================================
    // LOGIN SUPER ADMIN
    // =========================================================================

    /** @test */
    public function it_rejects_login_with_missing_fields(): void
    {
        $this->postJson('/api/login_super_admin', [], ['User-Agent' => self::VALID_UA])
             ->assertStatus(422);
    }

    /** @test */
    public function it_rejects_login_with_wrong_credentials(): void
    {
        $this->makeSuperAdmin();

        $this->postJson('/api/login_super_admin', [
            'email'    => 'superadmin@test.com',
            'password' => 'wrongpassword',
        ], ['User-Agent' => self::VALID_UA])
             ->assertStatus(400)
             ->assertJson(['message' => 'Email y/o clave no válidos.']);
    }

    /** @test */
    public function it_rejects_login_with_non_superadmin_user(): void
    {
        User::create([
            'name'         => 'Regular',
            'last_name'    => 'User',
            'email'        => 'regular@test.com',
            'password'     => Hash::make('password123'),
            'user_type_id' => UserType::ADMIN_EH,
        ]);

        $this->postJson('/api/login_super_admin', [
            'email'    => 'regular@test.com',
            'password' => 'password123',
        ], ['User-Agent' => self::VALID_UA])
             ->assertStatus(400);
    }

    /** @test */
    public function it_blocks_login_when_password_expired(): void
    {
        $this->makeSuperAdmin(['password_expired' => true]);

        $this->postJson('/api/login_super_admin', [
            'email'    => 'superadmin@test.com',
            'password' => 'password123',
        ], ['User-Agent' => self::VALID_UA])
             ->assertStatus(400)
             ->assertJsonFragment(['password_expired' => true]);
    }

    /** @test */
    public function it_allows_valid_superadmin_login(): void
    {
        $this->makeSuperAdmin();

        $this->postJson('/api/login_super_admin', [
            'email'    => 'superadmin@test.com',
            'password' => 'password123',
        ], ['User-Agent' => self::VALID_UA])
             ->assertStatus(200)
             ->assertJsonStructure(['access_token', 'token_type', 'expires_in']);
    }

    // =========================================================================
    // PASSWORD EXPIRED — FLUJO COMPLETO
    // =========================================================================

    /** @test */
    public function recover_password_clears_password_expired_flag(): void
    {
        $user = $this->makeSuperAdmin(['password_expired' => true]);

        $this->postJson('/api/recover-password', [
            'email' => 'superadmin@test.com',
        ], ['User-Agent' => self::VALID_UA])
             ->assertStatus(200);

        $this->assertFalse((bool) User::find($user->id)->password_expired);
    }

    /** @test */
    public function recover_password_returns_200_for_nonexistent_email(): void
    {
        // Prevención de email enumeration: siempre 200, nunca revelar si el email existe
        $this->postJson('/api/recover-password', [
            'email' => 'noexiste@test.com',
        ], ['User-Agent' => self::VALID_UA])
             ->assertStatus(200)
             ->assertJson(['message' => 'Correo enviado con exito.']);
    }

    // =========================================================================
    // RATE LIMITING — LOGIN
    // =========================================================================

    /** @test */
    public function it_rate_limits_login_after_10_attempts(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/login_super_admin', [
                'email'    => 'victim@test.com',
                'password' => 'wrong',
            ], ['User-Agent' => self::VALID_UA]);
        }

        $this->postJson('/api/login_super_admin', [
            'email'    => 'victim@test.com',
            'password' => 'wrong',
        ], ['User-Agent' => self::VALID_UA])
             ->assertStatus(429);
    }

    /** @test */
    public function rate_limit_is_per_email_not_per_ip(): void
    {
        // 10 intentos con email A
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/login_super_admin', [
                'email'    => 'victim_a@test.com',
                'password' => 'wrong',
            ], ['User-Agent' => self::VALID_UA]);
        }

        // Email B con la misma IP no debe estar bloqueado
        $this->postJson('/api/login_super_admin', [
            'email'    => 'victim_b@test.com',
            'password' => 'wrong',
        ], ['User-Agent' => self::VALID_UA])
             ->assertStatus(400); // 400, no 429
    }

    // =========================================================================
    // EMERGENCY RESET
    // =========================================================================

    /** @test */
    public function it_rejects_emergency_reset_for_non_superadmin(): void
    {
        $nonAdmin = User::create([
            'name'         => 'Admin',
            'last_name'    => 'EH',
            'email'        => 'admin@test.com',
            'password'     => Hash::make('password123'),
            'user_type_id' => UserType::ADMIN_EH,
        ]);
        $token = $this->tokenFor($nonAdmin);

        $this->postJson('/api/admin/emergency-reset-passwords', [
            'confirm'        => true,
            'admin_password' => 'password123',
        ], [
            'Authorization' => "Bearer {$token}",
            'User-Agent'    => self::VALID_UA,
        ])->assertStatus(403);
    }

    /** @test */
    public function it_rejects_emergency_reset_with_wrong_admin_password(): void
    {
        $admin = $this->makeSuperAdmin();
        $token = $this->tokenFor($admin);

        $this->postJson('/api/admin/emergency-reset-passwords', [
            'confirm'        => true,
            'admin_password' => 'wrongpassword',
        ], [
            'Authorization' => "Bearer {$token}",
            'User-Agent'    => self::VALID_UA,
        ])->assertStatus(403);
    }

    /** @test */
    public function it_executes_emergency_reset_and_sets_password_expired(): void
    {
        $admin = $this->makeSuperAdmin();
        $token = $this->tokenFor($admin);

        User::create([
            'name'         => 'Other',
            'last_name'    => 'User',
            'email'        => 'other@test.com',
            'password'     => Hash::make('original_password'),
            'user_type_id' => UserType::ADMIN_EH,
        ]);

        $this->postJson('/api/admin/emergency-reset-passwords', [
            'confirm'        => true,
            'admin_password' => 'password123',
        ], [
            'Authorization' => "Bearer {$token}",
            'User-Agent'    => self::VALID_UA,
        ])
             ->assertStatus(200)
             ->assertJsonStructure(['message', 'total', 'users']);

        $this->assertTrue(User::find($admin->id)->password_expired);
    }

    // =========================================================================
    // SECURITY HEADERS
    // =========================================================================

    /** @test */
    public function it_includes_security_headers_in_all_responses(): void
    {
        $response = $this->getJson('/api/room/images', ['User-Agent' => self::VALID_UA]);

        $response->assertHeader('X-Content-Type-Options', 'nosniff')
                 ->assertHeader('X-Frame-Options', 'DENY')
                 ->assertHeader('X-XSS-Protection', '1; mode=block')
                 ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }
}

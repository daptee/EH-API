# EH API — Documentación Técnica y de Negocio

> Última actualización: 2026-03-11
> Stack: Laravel 9 · PHP 8.0+ · MySQL · JWT (tymon/jwt-auth ^2.0)
> Servidor: Windows (WAMP) — sin SSH — migraciones se aplican vía phpMyAdmin

---

## Tabla de contenidos

1. [Contexto de negocio](#1-contexto-de-negocio)
2. [Arquitectura general](#2-arquitectura-general)
3. [Variables de entorno](#3-variables-de-entorno)
4. [Base de datos](#4-base-de-datos)
5. [Autenticación y roles](#5-autenticación-y-roles)
6. [Rutas de la API](#6-rutas-de-la-api)
7. [Controladores](#7-controladores)
8. [Modelos](#8-modelos)
9. [Middleware stack](#9-middleware-stack)
10. [Seguridad](#10-seguridad)
11. [Logs de seguridad](#11-logs-de-seguridad)
12. [Integración PMS (internal-api-eh)](#12-integración-pms-internal-api-eh)
13. [Flujos de negocio clave](#13-flujos-de-negocio-clave)
14. [Testing](#14-testing)
15. [Pendientes y optimizaciones](#15-pendientes-y-optimizaciones)

---

## 1. Contexto de negocio

**EH Boutique Experience** es un hotel boutique. Esta API es el backend de:

- El **panel de administración** del hotel (gestión de imágenes de habitaciones, usuarios admin).
- El **sistema de reservas** integrado con el PMS externo (Pxsol/PXSOL).
- El **front-end** del hotel y aplicaciones internas que consumen datos del PMS via un proxy autenticado (`internal-api-eh`).
- Comunicaciones por email: recupero de contraseña, confirmación de reservas, formularios de contacto, newsletter, envío de código a huéspedes.

**Actores del sistema:**

| Rol | `user_type_id` | Acceso |
|-----|----------------|--------|
| Superadmin | 1 | Panel completo, reset masivo de contraseñas, gestión de imágenes |
| Admin EH | 2 | Panel operativo del hotel |
| Admin Sukha | 3 | Panel del espacio Sukha |
| Admin Cafeteria | 4 | Panel de la cafetería |

---

## 2. Arquitectura general

```
Internet / Clientes
        │
        ▼
[ BotDetection ] ← Middleware global: bloquea scanners/bots
        │
[ SecurityHeaders ] ← HSTS, X-Frame-Options, etc.
        │
[ RequestSizeLimit ] ← 1MB JSON / 10MB multipart
        │
        ├─── Rutas públicas (sin auth)
        │         reservations, form/contact, newsletter, room/images (GET)
        │
        └─── Rutas autenticadas (jwt.verify + audit.log)
                  logout, room/images (POST/DELETE), clear-cache,
                  admin/emergency-reset-passwords,
                  /internal-api-eh/*,
                  reservations/by/reservation_number/{num}
                        │
                        ▼
               [ InternalApiController ] → HTTPS → PMS Pxsol
                  apieh.ehboutiqueexperience.com:9096 (DEV)
                  apieh.ehboutiqueexperience.com:8086 (PROD)
```

**Nota sobre el servidor:** El servidor es Windows + WAMP. No hay acceso SSH. Todas las migraciones de base de datos se aplican manualmente via phpMyAdmin.

---

## 3. Variables de entorno

```env
# App
APP_NAME=Laravel
APP_ENV=local
APP_ENVIRONMENT=DEV            # DEV o PROD — controla la URL del PMS
APP_KEY=                       # php artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost
FRONTEND_URL=http://localhost:3000

# Base de datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=

# Mail
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@ehboutiqueexperience.com"
MAIL_FROM_NAME="EH Boutique Experience"
MAIL_TO_CONTACT=contacto@example.com      # Destino de formulario de contacto
CONFIRMATION_EMAIL=confirmaciones@example.com  # Copia interna de reservas

# JWT
JWT_SECRET=                    # php artisan jwt:secret
JWT_TTL=60                     # Minutos de validez del token
JWT_REFRESH_TTL=20160          # 14 días para refresh
JWT_BLACKLIST_ENABLED=true

# Integraciones externas
MERCADOPAGO_USERS_DATA=
MERCADOPAGO_VINCULATION=
MP_ACCESS_TOKEN=
PXSOL_API_TOKEN=               # Token para el PMS Pxsol

# Seguridad
SECURITY_ALERT_EMAILS=admin@example.com  # Separados por coma; reciben alertas de rate limiting
```

---

## 4. Base de datos

### Tablas principales

#### `users`
```
id                 PK
name               string
last_name          string
email              string (unique)
password           string (bcrypt)
password_expired   tinyint(1) default 0   ← flag de reset masivo
phone              string nullable
profile_picture    string nullable         ← path relativo a /public
user_type_id       int nullable            ← FK a users_types
created_at, updated_at
```

#### `users_types`
```
id     PK   (1=Superadmin, 2=Admin EH, 3=Admin Sukha, 4=Admin Cafeteria)
name   string nullable
created_at, updated_at, deleted_at (soft delete)
```

#### `reservations`
```
id                  PK
reservation_number  string    ← número del PMS externo
status_id           int FK → reservations_status (default 1=INICIADA)
agency_user_id      int nullable FK → agency_users
created_at, updated_at, deleted_at (soft delete)
```

#### `reservations_status`
```
id    PK   (1=INICIADA, 2=CONFIRMADA, 3=CANCELADA, 4=RECHAZADA, 5=CANCELADO_AUTOMATICO)
name  string
created_at, updated_at
```

#### `reservations_status_history`
```
id              PK
reservation_id  int FK → reservations
status_id       int FK → reservations_status (default 1)
created_at, updated_at
```

#### `rejected_reservations`
```
id              PK
reservation_id  int FK → reservations
data            longText   ← motivo de rechazo
created_at, updated_at
```

#### `room_images`
```
id               PK
room_number      int nullable
url              string nullable   ← path relativo a /public
principal_image  int nullable      ← 1=principal, 0/null=secundaria
created_at, updated_at, deleted_at (soft delete)
```

#### `newsletter`
```
id          PK
email       string nullable
created_at, updated_at, deleted_at (soft delete)
```

#### `agency_users` *(módulo deshabilitado)*
```
id           PK
first_name   string
last_name    string
email        string (unique)
password     string (bcrypt, auto-hash en mutator)
agency_code  string
created_at, updated_at
```

### SQL de mantenimiento

Migración manual necesaria al actualizar desde versiones anteriores a la auditoría de seguridad:
```sql
ALTER TABLE `users`
ADD COLUMN `password_expired` TINYINT(1) NOT NULL DEFAULT 0 AFTER `password`;
```

---

## 5. Autenticación y roles

### Flujo de login
1. `POST /api/login_super_admin` con `{email, password}`
2. El middleware `throttle:login` limita a **10 intentos/min por email**
3. El controller busca el usuario por email
4. Verifica que `user_type_id === 1` (SUPERADMIN) → 400 si no
5. Verifica que `password_expired === false` → 400 + `{password_expired: true}` si es true
6. Intenta `JWTAuth::attempt(credentials)` → 400 si falla
7. Devuelve `{access_token, token_type: "bearer", expires_in, user}`

### Token JWT
- Duración: `JWT_TTL` minutos (default: 60)
- Se invalida al hacer logout (blacklist activado)
- Guard por defecto: `users` (modelo `User`)

### Guard de agencias *(deshabilitado)*
- Guard: `agency` con modelo `AgencyUser`
- Rutas comentadas en `routes/api.php`

### `password_expired` — flujo completo
```
EmergencyController::resetAllPasswords()
    → marca password_expired = true en TODOS los usuarios
    → devuelve lista de {email, nueva_contraseña} (única vez)

Login super admin detecta password_expired = true
    → responde 400 + {password_expired: true}

Front-end redirige al usuario a /recover-password

POST /api/recover-password {email}
    → genera nueva contraseña random
    → password_expired = false  ← limpia el flag
    → envía contraseña por email
```

---

## 6. Rutas de la API

### Públicas (sin autenticación)

| Método | Ruta | Controlador | Notas |
|--------|------|-------------|-------|
| POST | `/api/login_super_admin` | AuthController::login_super_admin | throttle:login (10/min por email) |
| POST | `/api/recover-password` | UserController::recover_password_user | throttle:mail_send (5/min por IP) |
| POST | `/api/send/code/email` | UserController::send_code_email | throttle:mail_send |
| GET | `/api/room/images` | RoomController::all_images_rooms | |
| GET | `/api/room/images/{room_id}` | RoomController::room_images | |
| GET | `/api/room/images_principal` | RoomController::room_images_principal | |
| POST | `/api/reservations` | ReservationController::store | |
| POST | `/api/reservations/confirm` | ReservationController::confirm_reservation | |
| POST | `/api/reservations/payment/rejection` | ReservationController::payment_rejection | |
| POST | `/api/reservations/cancel` | ReservationController::cancel_reservation | |
| GET | `/api/reservations/status/list` | ReservationController::get_status_list | |
| POST | `/api/form/contact` | FormController::form_contact | |
| POST | `/api/newsletter/register/email` | NewsletterController::newsletter_register_email | |
| POST | `/api/matriz-design/send-form` | FormController::matriz_design | |

### Autenticadas (jwt.verify + audit.log)

| Método | Ruta | Controlador | Notas |
|--------|------|-------------|-------|
| POST | `/api/logout` | AuthController::logout | |
| POST | `/api/room/images` | RoomController::store | |
| POST | `/api/room/images/delete/{image_id}` | RoomController::room_images_delete | |
| GET | `/api/clear-cache` | Artisan clear:all | |
| GET | `/api/test-mail` | inline closure | Solo env no-producción |
| POST | `/api/admin/emergency-reset-passwords` | EmergencyController::resetAllPasswords | Solo SUPERADMIN |
| GET | `/api/reservations/by/reservation_number/{num}` | ReservationController::by_reservation_number | |

### Internal API — Proxy al PMS (jwt.verify + audit.log)

Todas bajo el prefijo `/api/internal-api-eh/`:

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/Naciones` | Lista de países del PMS |
| GET | `/Naciones2` | Lista de países (variante) |
| GET | `/Tarifas` | Tarifas de habitaciones |
| GET | `/Disponibilidad` | Disponibilidad de habitaciones |
| GET | `/ReservaxCodigo` | Reserva por código de reserva |
| GET | `/PedidoxCodigo` | Pedido por código |
| GET | `/Articulos` | Artículos del menú |
| GET | `/Articulo` | Artículo individual |
| GET | `/Rubros` | Categorías del menú |
| GET | `/ArticulosDestacados` | Artículos destacados |
| GET | `/ArticulosDesayunos` | Artículos de desayuno |
| GET | `/TiposDocumentos` | Tipos de documentos |
| GET | `/Pedidos` | Lista de pedidos |
| GET | `/Habitaciones` | Lista de habitaciones |
| GET | `/Reservas` | Lista de reservas |
| GET | `/Calendario` | Calendario de disponibilidad |
| GET | `/ReservaxOExterna` | Reserva por orden externa |
| GET | `/ReservaActiva` | Reserva activa del usuario |
| GET | `/Agencias` | Lista de agencias |
| POST | `/IniciaReserva` | Inicia una reserva en el PMS |
| POST | `/CancelaReserva` | Cancela una reserva |
| POST | `/ConfirmaReserva` | Confirma una reserva |
| POST | `/ConfirmaPasajeros` | Confirma datos de pasajeros |
| POST | `/IniciaPedido` | Inicia un pedido en el PMS |
| POST | `/CancelaPedido` | Cancela un pedido |
| POST | `/ConfirmaPedido` | Confirma un pedido |
| POST | `/RealizaCheck` | Realiza check-in/out |
| POST | `/CreaReservaAgencias` | Crea reserva para agencias |

### Módulo de agencias *(deshabilitado)*

Rutas comentadas en `routes/api.php`. Guard `agency`, modelo `AgencyUser`. Pendiente reactivar cuando el módulo esté listo.

---

## 7. Controladores

### AuthController
**Archivo:** `app/Http/Controllers/AuthController.php`

- `login_super_admin(LoginRequest $request)` — Valida credenciales, verifica tipo SUPERADMIN, chequea `password_expired`, genera JWT. Registra intentos fallidos via `SecurityLogger`.
- `logout()` — Invalida el token JWT en la blacklist.
- `respondWithToken($token, $id)` — Helper privado que construye la respuesta estándar `{access_token, token_type, expires_in, user}`.

---

### UserController
**Archivo:** `app/Http/Controllers/UserController.php`

- `recover_password_user(Request)` — Genera contraseña random de 16 chars, la hashea, limpia `password_expired`, envía mail. **Siempre responde 200** (previene email enumeration).
- `show($id)` — Devuelve usuario con relación `user_type`.
- `update(Request)` — Actualiza perfil: name, last_name, email, phone, locality_id, password.
- `update_profile_picture(Request)` — Sube imagen a `public/users/profiles/`.
- `send_code_email(Request)` — Envía código de verificación por email al huésped. Throttle 5/min por IP.

---

### ReservationController
**Archivo:** `app/Http/Controllers/ReservationController.php`

- `store(Request)` — Crea reserva con estado INICIADA (1). Guarda en `reservations` y `reservations_status_history`.
- `confirm_reservation(Request)` — Cambia estado a CONFIRMADA (2). Envía mails a: email interno, agencia (si aplica), y cliente.
- `payment_rejection(Request)` — Cambia estado a RECHAZADA (4). Guarda motivo en `rejected_reservations`.
- `cancel_reservation(Request)` — Cambia estado a CANCELADA (3). Registra en historial.
- `get_status_list()` — Devuelve todos los estados disponibles.
- `by_reservation_number($num)` — Busca reserva por número de PMS. Incluye `status_history` y `rejected_history`. **Requiere JWT.**

---

### RoomController
**Archivo:** `app/Http/Controllers/RoomController.php`

- `store(Request)` — Sube imágenes de habitaciones. Valida formato horizontal y ancho máximo 1600px. Guarda en `public/rooms/{room_number}/images/`.
- `room_images($room_number)` — GET de imágenes de una habitación específica.
- `all_images_rooms()` — GET de todas las imágenes de todas las habitaciones.
- `room_images_principal()` — GET solo de imágenes marcadas como principal.
- `room_images_delete($image_id)` — Soft delete de una imagen.

---

### EmergencyController
**Archivo:** `app/Http/Controllers/EmergencyController.php`

- `resetAllPasswords(Request)` — **Solo SUPERADMIN.** Requiere confirmación con la contraseña actual del admin. Resetea contraseñas de todos los usuarios a strings random de 12 chars. Marca `password_expired = true`. Devuelve lista `{email, nueva_contraseña}` **una sola vez**. Registra en `SecurityLogger`.

---

### InternalApiController
**Archivo:** `app/Http/Controllers/InternalApiController.php`

Actúa como **proxy autenticado** hacia el PMS externo (Pxsol). No almacena datos en la DB local.

- `get_url()` — Retorna la URL base según `APP_ENVIRONMENT`: DEV (`:9096`) o PROD (`:8086`).
- `fetchDataFromApi($endpoint, $params, $method)` — Método central. GET usa Guzzle, POST usa cURL. SSL verify activo (`'verify' => true`).
- `transformParams($params)` — Convierte `null` y `""` a `""` recursivamente antes de enviar al PMS.
- Cada endpoint del PMS tiene su método propio que llama a `fetchDataFromApi()`.

**URLs del PMS:**
- DEV: `https://apieh.ehboutiqueexperience.com:9096/api/v1/`
- PROD: `https://apieh.ehboutiqueexperience.com:8086/api/v1/`

---

### FormController
**Archivo:** `app/Http/Controllers/FormController.php`

- `form_contact(Request)` — Valida name/email/message. Envía mail a `MAIL_TO_CONTACT`.
- `matriz_design(Request)` — Envía formulario de Matriz Design al email configurado.

---

### NewsletterController
**Archivo:** `app/Http/Controllers/NewsletterController.php`

- `newsletter_register_email(Request)` — Registra email en tabla `newsletter`. Previene duplicados.

---

### AgencyAuthController *(módulo deshabilitado)*
**Archivo:** `app/Http/Controllers/AgencyAuthController.php`

Guard `agency`, modelo `AgencyUser`. Contiene: register, login, logout, recover_password, update_profile. Las rutas están comentadas.

---

## 8. Modelos

### User
- `user_type_id` → relación con `UserType`
- `password_expired` → cast boolean
- Implements `JWTSubject`
- Método estático: `getAllDataUser($id)`

### UserType
- Constantes: `SUPERADMIN=1`, `ADMIN_EH=2`, `ADMIN_SUKHA=3`, `ADMIN_CAFETERIA=4`

### Reservation
- Relaciones: `status()`, `agency_user()`, `status_history()`, `rejected_history()`
- Método estático: `getAllReservation($id)`

### ReservationStatus
- Constantes: `INICIADA=1`, `CONFIRMADA=2`, `CANCELADA=3`, `RECHAZADA=4`, `CANCELADO_AUTOMATICO=5`

### ReservationStatusHistory
- Método estático: `saveHistoryStatusReservation($reservation_id, $status_id)`

### RejectedReservation
- Método estático: `saveReasonRejection($reservation_id, $reason)`

### RoomImage
- Soft delete
- `principal_image`: 1=principal, 0/null=secundaria

### Newsletter
- Soft delete

### AgencyUser *(módulo deshabilitado)*
- Mutator automático de password (auto-hash)
- Implements `JWTSubject`

---

## 9. Middleware stack

### Global (todo request)

```
TrustProxies              → configurar si hay Cloudflare/proxy delante
HandleCors                → CORS (actualmente open: ['*'])
BotDetection              → bloquea UA vacío y 29 patrones de herramientas de ataque
PreventRequestsDuringMaintenance
ValidatePostSize          → Laravel estándar
TrimStrings
ConvertEmptyStringsToNull
SecurityHeaders           → headers de seguridad HTTP
RequestSizeLimit          → 1MB JSON / 10MB multipart
```

### Por ruta (nombrados)

| Alias | Clase | Descripción |
|-------|-------|-------------|
| `jwt.verify` | JwtMiddleware | Valida token JWT; 401 si ausente/inválido/expirado |
| `audit.log` | AccessAuditLog | Log de acceso autenticado + rutas sensibles; usa `terminate()` |
| `throttle:login` | ThrottleRequests | 10/min por email |
| `throttle:mail_send` | ThrottleRequests | 5/min por IP |
| `throttle:api` | ThrottleRequests | 60/min por usuario (grupo api) |

---

## 10. Seguridad

### BotDetection
Bloquea (403) si el `User-Agent` está vacío o contiene alguno de los 29 patrones de herramientas conocidas: sqlmap, nikto, nuclei, nmap, burpsuite, wfuzz, metasploit, dirbuster, gobuster, acunetix, nessus, shodan, masscan, zaproxy, w3af, hydra, medusa, python-requests, curl (variantes de scripts), etc.

### SecurityHeaders
Todas las respuestas incluyen:
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `X-XSS-Protection: 1; mode=block`
- `Strict-Transport-Security: max-age=31536000; includeSubDomains`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy: geolocation=(), microphone=(), camera=()`

### Rate Limiting
- **login**: 10 intentos/min por dirección de email (no por IP — resiste rotación de IPs)
- **mail_send**: 5 requests/min por IP (recover-password, send/code/email)
- Al alcanzar el límite: log + email de alerta a `SECURITY_ALERT_EMAILS`

### JWT
- Tokens de 60 minutos con blacklist activada
- `lock_subject: true` previene impersonación
- Logout invalida el token inmediatamente

---

## 11. Logs de seguridad

**Directorio:** `storage/logs/security/`
**Rotación:** mensual (un archivo por mes)

| Archivo | Contenido |
|---------|-----------|
| `failed-logins-YYYY-MM.log` | Intentos fallidos de login (credenciales incorrectas, tipo usuario incorrecto, password expirado, rate limit) |
| `bot-detection-YYYY-MM.log` | UA vacíos y patrones de herramientas de ataque detectados |
| `sensitive-requests-YYYY-MM.log` | Accesos a rutas sensibles (login, logout, reset, imágenes, emergency-reset) y acciones de admin |
| `access-audit-YYYY-MM.log` | Todo acceso a rutas autenticadas (jwt.verify) |

**Formato de línea:**
```
[2026-03-11 14:32:01] FAILED_LOGIN | email:x@x.com | ip:127.0.0.1 | reason:wrong_credentials | ua:Mozilla/5.0...
```

---

## 12. Integración PMS (internal-api-eh)

El hotel utiliza el PMS **Pxsol** alojado en `apieh.ehboutiqueexperience.com`. Esta API actúa como **proxy autenticado**: los clientes (panel de admin, apps internas) no llaman al PMS directamente; pasan por esta API con su JWT.

**Autenticación hacia el PMS:** header `api-token` con el valor de `PXSOL_API_TOKEN`.

**Ambientes:**
- DEV → puerto 9096
- PROD → puerto 8086

**Comportamiento en caso de error del PMS:** el controller devuelve la respuesta del PMS tal cual (incluyendo errores 4xx/5xx). Los tests de seguridad verifican que la capa de autenticación funcione correctamente (no devuelva 401 con token válido), independientemente del estado del PMS.

**SSL:** `verify: true` en Guzzle y cURL. Si el certificado del PMS es inválido en producción, cambiar a `verify: false` en `InternalApiController::fetchDataFromApi()`.

---

## 13. Flujos de negocio clave

### Flujo de reserva

```
Cliente → POST /api/reservations
    → Crea Reservation (status=INICIADA)
    → Crea ReservationStatusHistory

Pago aprobado → POST /api/reservations/confirm
    → status=CONFIRMADA
    → Email a: CONFIRMATION_EMAIL (interno), agency (si aplica), cliente

Pago rechazado → POST /api/reservations/payment/rejection
    → status=RECHAZADA
    → Guarda motivo en RejectedReservation

Cancelación → POST /api/reservations/cancel
    → status=CANCELADA
    → Registra historial
```

### Flujo de operación en el PMS

```
Admin (con JWT) → GET /api/internal-api-eh/Disponibilidad
    → JwtMiddleware valida token
    → InternalApiController::fetchDataFromApi() → HTTPS → PMS Pxsol
    → Respuesta del PMS devuelta al cliente
```

### Flujo de imágenes de habitaciones

```
Admin (con JWT) → POST /api/room/images
    → Valida: formato horizontal, ancho ≤1600px
    → Guarda en public/rooms/{room_number}/images/{uuid}.{ext}
    → Crea RoomImage (url, room_number, principal_image)

Front público → GET /api/room/images
    → Devuelve todas las imágenes (sin auth)
```

### Flujo de reset masivo de contraseñas

```
Superadmin (con JWT) → POST /api/admin/emergency-reset-passwords
    body: { confirm: true, admin_password: "su_contraseña_actual" }

    1. Verifica user_type_id === SUPERADMIN
    2. Verifica Hash::check(admin_password, admin->password)
    3. Por cada usuario: genera Str::random(12), hashea, marca password_expired=true
    4. Responde {message, total, users: [{email, new_password}]}
       ← esta lista NO se vuelve a mostrar
    5. Log en SecurityLogger::adminAction

Usuarios intentan login
    → AuthController detecta password_expired=true → 400 + {password_expired: true}
    → Front redirige a /recover-password

POST /api/recover-password {email: "x@x.com"}
    → Genera nueva contraseña random
    → password_expired = false
    → Envía contraseña por email
    → Siempre responde 200 (previene email enumeration)
```

---

## 14. Testing

### Configuración
- Framework: PHPUnit 9
- DB: SQLite in-memory (configurado en `phpunit.xml`)
- Las migraciones incluyen seed de `users_types` (IDs 1-4)

### Suite de seguridad
**Archivo:** `tests/Feature/Security/SecurityTest.php`
**34 tests** cubriendo:

| Categoría | Tests |
|-----------|-------|
| Bot detection | UA vacío, sqlmap, nikto, nuclei, UA válido |
| Rutas sin token | logout, room/images POST+DELETE, clear-cache, emergency-reset |
| internal-api-eh sin token | Articulos, Rubros, Pedidos, Reservas, Calendario, IniciaReserva, IniciaPedido |
| Token válido/inválido | Pasa middleware con token válido, rechaza token inválido |
| Login super admin | Campos faltantes (422), credenciales malas (400), no-superadmin (400), password expirado (400), login exitoso (200) |
| Recover password | Limpia `password_expired`, siempre 200 (email enumeration) |
| Rate limiting | 10 intentos → 429, rate limit por email (no por IP) |
| Emergency reset | No-superadmin (403), contraseña incorrecta (403), éxito (200 + structure) |
| Security headers | X-Content-Type-Options, X-Frame-Options, X-XSS-Protection, Referrer-Policy |

### Ejecutar tests
```bash
php artisan test
# o solo la suite de seguridad:
php artisan test --filter SecurityTest
```

---

## 15. Pendientes y optimizaciones

### Críticos (hacer antes del próximo deploy)

- [ ] **Actualizar dependencias vulnerables** (11 CVEs detectados por GitHub Dependabot):
  ```bash
  composer update laravel/framework symfony/http-foundation symfony/http-kernel symfony/process league/commonmark
  ```
- [ ] **Verificar SSL del PMS en producción**: Con `APP_ENVIRONMENT=PROD`, `InternalApiController` llama a `apieh.ehboutiqueexperience.com:8086` con `verify:true`. Si el certificado del PMS es autofirmado o inválido, todas las llamadas fallarán con excepción SSL. Probar haciendo login en el panel y ejecutando cualquier endpoint de `internal-api-eh`. Si falla, temporalmente revertir a `verify: false` en `fetchDataFromApi()`.

### Seguridad

- [ ] **Restringir CORS**: `config/cors.php` tiene `allowed_origins: ['*']`. Cuando se conozcan los dominios de producción del front-end, cambiar a lista explícita: `['https://panel.ehboutiqueexperience.com', 'https://www.ehboutiqueexperience.com']`.
- [ ] **TrustProxies**: Si hay Cloudflare delante, configurar `$proxies = '*'` en `app/Http/Middleware/TrustProxies.php` para que `$request->ip()` devuelva la IP real del cliente y no la de Cloudflare.
- [ ] **OTP para cambios de email/contraseña en perfil de agencia**: Implementar cuando se reactive el módulo de agencias.
- [ ] **Eliminar endpoint `GET /api/test-mail`**: Actualmente llama a `Mail::to("slarramendy@daptee.com.ar")` con email hardcodeado. Bloquear o eliminar en producción.

### Optimizaciones de código

- [ ] **Extraer helper `save_image_public_folder`**: El método existe duplicado en `UserController` y `RoomController` con lógica idéntica. Mover a un trait `SavesImages` o a un Service `ImageStorageService`.

- [ ] **Centralizar `respondWithToken`**: El método `respondWithToken($token, $id)` existe duplicado en `AuthController` y `AgencyAuthController`. Extraer a un trait `JwtResponds`.

- [ ] **Validación de requests con Form Requests**: Varios controllers usan `$request->validate([...])` inline. Mover a clases `FormRequest` dedicadas (ej. `LoginRequest` ya existe, replicar para `EmergencyResetRequest`, `ReservationStoreRequest`, etc.) para separar responsabilidades.

- [ ] **Constantes de estados de reserva en `ReservationStatus`**: Los estados se usan con `ReservationStatus::INICIADA` pero algunos controllers usan el número literal `1` directamente. Unificar siempre usando las constantes del modelo.

- [ ] **Eliminar rutas comentadas de agency del archivo de rutas**: El bloque comentado ocupa ~30 líneas en `routes/api.php`. Moverlo a un archivo `routes/agency.php` separado (aunque deshabilitado) para mantener `api.php` más limpio.

- [ ] **Método `getAllDataUser` y `getAllReservation`**: Son métodos estáticos en los modelos que devuelven el propio modelo con relaciones. Considerar reemplazarlos con scopes de Eloquent (`scopeWithRelations`) o directamente `User::with('user_type')->findOrFail($id)` en el controller, eliminando la indirección innecesaria.

- [ ] **Paginación en endpoints de listado**: `InternalApiController` devuelve listas completas del PMS sin paginación. Si el PMS lo soporta, agregar parámetros de paginación para no cargar colecciones grandes en memoria.

- [ ] **Cache de respuestas del PMS**: Endpoints de solo lectura del PMS (Naciones, TiposDocumentos, Rubros, ArticulosDestacados) devuelven datos que cambian raramente. Agregar cache de corta duración (5-15 min) para reducir llamadas al PMS externo.

### Módulo de agencias (cuando se reactive)

- [ ] **OTP para cambio de email/contraseña**: Antes de guardar un nuevo email o contraseña en el perfil de agencia, enviar código OTP al email actual para confirmar identidad.
- [ ] **Completar tests de agencias**: Agregar suite de tests equivalente a `SecurityTest` para el módulo de agencias.
- [ ] **Revisar `agency_user_id` en reservas**: La columna existe en `reservations` pero las rutas de agencia están deshabilitadas. Verificar que el flujo de reserva de agencia funcione correctamente al reactivar.

### Infraestructura

- [ ] **Logs en producción**: Confirmar que `storage/logs/security/` tenga permisos de escritura correctos en el servidor WAMP. El directorio se crea automáticamente, pero en Windows puede requerir ajuste de permisos.
- [ ] **Rotación de logs del sistema**: Los logs de seguridad rotan mensualmente por diseño, pero los logs de Laravel (`storage/logs/laravel.log`) pueden crecer. Configurar rotación diaria en `config/logging.php`.
- [ ] **Alertas de JWT expirado**: Actualmente si el token expira, el middleware devuelve 401 y el frontend debe manejar el refresh. Documentar este comportamiento para los equipos de front-end que consumen la API.

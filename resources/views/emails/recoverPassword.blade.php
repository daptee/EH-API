<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Restablecimiento de contraseña</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; color: #333; }
        .container { max-width:600px; margin:0 auto; padding:20px; }
        .header { padding-bottom:10px; border-bottom:1px solid #eee; }
        .content { margin-top:20px; }
        .password { display:inline-block; background:#f4f4f4; padding:8px 12px; border-radius:4px; font-family:monospace; }
        .footer { margin-top:30px; font-size:12px; color:#777; }
        a.btn { display:inline-block; background:#1a73e8; color:#fff; padding:10px 16px; border-radius:4px; text-decoration:none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Restablecimiento de contraseña</h2>
        </div>
        <div class="content">
            <p>Hola {{ $data['name'] }},</p>
            <p>Hemos generado una nueva contraseña temporal para tu cuenta. Por favor, úsala para ingresar y luego cambia tu contraseña desde tu perfil.</p>

            <p>Cuenta (email): <strong>{{ $data['email'] }}</strong></p>

            <p>Contraseña temporal: <span class="password">{{ $data['password'] }}</span></p>

            <p>Por seguridad, te recomendamos cambiar esta contraseña lo antes posible y no compartirla con terceros.</p>
        </div>
        <div class="footer">
            <p>Si no solicitaste este cambio, por favor contacta con nuestro equipo de soporte.</p>
            <p>Saludos,<br>Equipo de soporte</p>
        </div>
    </div>
</body>
</html>
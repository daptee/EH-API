<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código de verificación</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; background-color: #f5f5f5; color: #333; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 6px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background-color: #1a1a1a; padding: 28px 40px; }
        .header h1 { color: #ffffff; font-size: 18px; font-weight: 600; letter-spacing: 0.5px; }
        .body { padding: 36px 40px; }
        .body p { font-size: 15px; line-height: 1.6; color: #444; }
        .otp-block { margin: 28px 0; text-align: center; }
        .otp-code { display: inline-block; background: #f0f0f0; border: 1px solid #ddd; border-radius: 6px; padding: 14px 32px; font-size: 36px; font-weight: 700; letter-spacing: 10px; font-family: 'Courier New', Courier, monospace; color: #1a1a1a; }
        .expiry { margin-top: 6px; font-size: 13px; color: #888; }
        .footer { padding: 20px 40px; border-top: 1px solid #eee; }
        .footer p { font-size: 12px; color: #aaa; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>Verificación de acceso</h1>
        </div>
        <div class="body">
            <p>Hola {{ $name }},</p>
            <p style="margin-top: 16px;">Ingresá el siguiente código para completar tu inicio de sesión:</p>

            <div class="otp-block">
                <div class="otp-code">{{ $otp }}</div>
                <p class="expiry">Válido por 10 minutos.</p>
            </div>

            <p>Si no intentaste iniciar sesión, ignorá este correo. Tu cuenta permanece segura.</p>
        </div>
        <div class="footer">
            <p>Este mensaje fue generado automáticamente. Por favor no respondas a este correo.</p>
        </div>
    </div>
</body>
</html>

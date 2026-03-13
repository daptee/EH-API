<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'EH Boutique Experience')</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; background-color: #e8eaed; color: #333333; }

        .outer { padding: 40px 20px; max-width: 640px; margin: 0 auto; }

        /* Logo sobre el card */
        .email-logo { text-align: center; margin-bottom: 24px; }
        .email-logo img { max-height: 72px; width: auto; }

        /* Card blanco */
        .card { background: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }

        /* Body */
        .card-body { padding: 40px 44px 32px; }
        .card-body p { font-size: 15px; line-height: 1.7; color: #444444; margin-bottom: 14px; }
        .card-body p:last-child { margin-bottom: 0; }
        .card-body strong { color: #3E4251; }

        /* Highlight block (códigos, contraseñas, datos importantes) */
        .highlight-block { margin: 28px 0; text-align: center; }
        .highlight-label { font-size: 11px; font-weight: 700; letter-spacing: 1.5px; color: #888888; text-transform: uppercase; margin-bottom: 12px; }
        .highlight-value {
            display: inline-block;
            background: #f5f6f8;
            border: 2px solid #3E4251;
            border-radius: 6px;
            padding: 14px 36px;
            font-size: 34px;
            font-weight: 700;
            letter-spacing: 10px;
            font-family: 'Courier New', Courier, monospace;
            color: #3E4251;
        }
        .highlight-sublabel { margin-top: 10px; font-size: 13px; color: #888888; }

        /* Alert block */
        .alert-block { background: #f5f6f8; border-radius: 6px; padding: 14px 18px; margin: 20px 0; font-size: 14px; color: #555555; line-height: 1.6; }

        /* Data table */
        .data-table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 14px; }
        .data-table td { padding: 8px 0; vertical-align: top; border-bottom: 1px solid #f0f0f0; }
        .data-table tr:last-child td { border-bottom: none; }
        .data-table td:first-child { color: #888888; width: 140px; }
        .data-table td:last-child { color: #333333; font-weight: 600; }

        /* Footer de contacto — dentro del card */
        .card-footer { padding: 24px 44px 32px; border-top: 1px solid #eeeeee; }
        .card-footer p { font-size: 13px; color: #666666; line-height: 1.9; }
        .card-footer a { color: #3E4251; text-decoration: none; }
        .footer-separator { border: none; border-top: 1px solid #e0e0e0; margin: 18px 0; }
        .thank-you { font-size: 14px; color: #3E4251; font-weight: 600; text-align: center; }

        /* Copy — fuera del card, sobre el fondo gris */
        .card-copy { text-align: center; margin-top: 18px; padding-bottom: 8px; }
        .card-copy p { font-size: 12px; color: #9a9ea8; }
        .card-copy a { color: #3E4251; text-decoration: none; }
        .card-copy a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="outer">

        <div class="email-logo">
            <img src="{{ asset('images/isologo.png') }}" alt="EH Boutique Experience">
        </div>

        <div class="card">
            <div class="card-body">
                @yield('content')
            </div>

            <div class="card-footer">
                <p>Ante cualquier consulta, no dudes en contactarnos.</p>
                <p>
                    <strong>Contacto:</strong> <a href="mailto:info@ehboutiqueexperience.com">info@ehboutiqueexperience.com</a><br>
                    <strong>WhatsApp:</strong> +54 9 2966 753463<br>
                    <strong>Teléfono:</strong> +54 9 2966 753463
                </p>
                <hr class="footer-separator">
                <p class="thank-you">Muchas gracias por haber elegido EH Boutique Experience</p>
            </div>
        </div>

        <div class="card-copy">
            <p>
                <a href="https://www.ehboutiqueexperience.com" target="_blank">www.ehboutiqueexperience.com</a>
                &nbsp;·&nbsp;
                Desarrollado por <a href="https://daptee.com.ar" target="_blank">Daptee</a>
            </p>
        </div>

    </div>
</body>
</html>

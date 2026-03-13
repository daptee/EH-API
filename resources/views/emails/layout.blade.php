<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'EH Boutique Experience')</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; background-color: #f0f2f5; color: #333333; }
        .outer { padding: 40px 20px; }
        .card { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }

        /* Header */
        .card-header { background-color: #ffffff; padding: 28px 40px; text-align: center; border-bottom: 3px solid #3E4251; }
        .card-header img { max-height: 70px; width: auto; }

        /* Body */
        .card-body { padding: 36px 40px; }
        .card-body p { font-size: 15px; line-height: 1.7; color: #444444; margin-bottom: 14px; }
        .card-body p:last-child { margin-bottom: 0; }
        .card-body strong { color: #3E4251; }

        /* Highlight block (para códigos, contraseñas, datos importantes) */
        .highlight-block { margin: 28px 0; text-align: center; }
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
        .highlight-label { margin-top: 8px; font-size: 13px; color: #888888; }

        /* Alert block */
        .alert-block { background: #fff8e1; border-left: 4px solid #f5a623; border-radius: 4px; padding: 12px 16px; margin: 20px 0; font-size: 14px; color: #7a5f00; }

        /* Data table (para listas de datos tipo check-in, check-out, etc.) */
        .data-table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 14px; }
        .data-table td { padding: 8px 0; vertical-align: top; }
        .data-table td:first-child { color: #888888; width: 160px; }
        .data-table td:last-child { color: #333333; font-weight: 600; }

        /* Footer de contacto */
        .card-footer { padding: 28px 40px; border-top: 1px solid #eeeeee; }
        .card-footer p { font-size: 13px; color: #666666; line-height: 1.8; margin-bottom: 0; }
        .card-footer .footer-separator { border: none; border-top: 1px solid #dddddd; margin: 16px 0; }
        .card-footer .thank-you { font-size: 14px; color: #3E4251; font-weight: 600; text-align: center; margin-top: 4px; }

        /* Copy */
        .card-copy { max-width: 600px; margin: 14px auto 0; text-align: center; }
        .card-copy p { font-size: 12px; color: #999999; }
        .card-copy a { color: #3E4251; text-decoration: none; }
        .card-copy a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="outer">
        <div class="card">

            <div class="card-header">
                <img src="{{ asset('images/isologo.png') }}" alt="EH Boutique Experience">
            </div>

            <div class="card-body">
                @yield('content')
            </div>

            <div class="card-footer">
                <p>Ante cualquier consulta, no dudes en contactarnos.</p>
                <p>
                    <strong>Contacto:</strong> info@ehboutiqueexperience.com<br>
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

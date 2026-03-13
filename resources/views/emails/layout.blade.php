<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'EH Boutique Experience')</title>
    <style>
        /* Estilos no críticos — para clientes que los respetan */
        .data-table td:first-child { color: #888888; width: 140px; }
        .data-table td:last-child { color: #333333; font-weight: 600; }
        .data-table td { padding: 8px 0; vertical-align: top; border-bottom: 1px solid #f0f0f0; }
        .data-table tr:last-child td { border-bottom: none; }
        .highlight-label { font-size: 11px; font-weight: 700; letter-spacing: 1.5px; color: #888888; text-transform: uppercase; margin-bottom: 12px; }
        .highlight-sublabel { margin-top: 10px; font-size: 13px; color: #888888; }
        .alert-block { background: #f5f6f8; border-radius: 6px; padding: 14px 18px; margin: 20px 0; font-size: 14px; color: #555555; line-height: 1.6; }
        a { color: #3E4251; text-decoration: none; }
    </style>
</head>
<body bgcolor="#e8eaed" style="margin:0; padding:0; background-color:#e8eaed;">

    {{-- Wrapper full-width con fondo gris --}}
    <table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#e8eaed" style="background-color:#e8eaed;">
        <tr>
            <td align="center" style="padding: 40px 20px;">

                {{-- Logo sobre el card --}}
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;">
                    <tr>
                        <td align="center" style="padding-bottom: 24px;">
                            <img src="{{ asset('images/isologo.png') }}" alt="EH Boutique Experience" style="max-height:72px; width:auto; display:block;">
                        </td>
                    </tr>
                </table>

                {{-- Card blanco --}}
                <table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#ffffff" style="max-width:600px; background-color:#ffffff; border-radius:10px; box-shadow:0 2px 12px rgba(0,0,0,0.08); overflow:hidden;">

                    {{-- Body --}}
                    <tr>
                        <td style="padding: 40px 44px 32px; font-family: Arial, Helvetica, sans-serif; font-size:15px; line-height:1.7; color:#444444;">
                            @yield('content')
                        </td>
                    </tr>

                    {{-- Separador --}}
                    <tr>
                        <td style="padding: 0 44px;">
                            <hr style="border:none; border-top:1px solid #eeeeee; margin:0;">
                        </td>
                    </tr>

                    {{-- Footer de contacto --}}
                    <tr>
                        <td style="padding: 24px 44px 32px; font-family: Arial, Helvetica, sans-serif; font-size:13px; line-height:1.9; color:#666666;">
                            <p style="margin:0 0 8px;">Ante cualquier consulta, no dudes en contactarnos.</p>
                            <p style="margin:0 0 16px;">
                                <strong style="color:#3E4251;">Contacto:</strong> <a href="mailto:info@ehboutiqueexperience.com" style="color:#3E4251; text-decoration:none;">info@ehboutiqueexperience.com</a><br>
                                <strong style="color:#3E4251;">WhatsApp:</strong> +54 9 2966 753463<br>
                                <strong style="color:#3E4251;">Teléfono:</strong> +54 9 2966 753463
                            </p>
                            <hr style="border:none; border-top:1px solid #e0e0e0; margin:0 0 16px;">
                            <p style="margin:0; text-align:center; font-size:14px; font-weight:700; color:#3E4251;">
                                Muchas gracias por haber elegido EH Boutique Experience
                            </p>
                        </td>
                    </tr>

                </table>

                {{-- Copy fuera del card --}}
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;">
                    <tr>
                        <td align="center" style="padding-top: 18px; font-family: Arial, Helvetica, sans-serif; font-size:12px; color:#9a9ea8;">
                            <a href="https://www.ehboutiqueexperience.com" target="_blank" style="color:#3E4251; text-decoration:none;">www.ehboutiqueexperience.com</a>
                            &nbsp;·&nbsp;
                            Desarrollado por <a href="https://daptee.com.ar" target="_blank" style="color:#3E4251; text-decoration:none;">Daptee</a>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>

</body>
</html>

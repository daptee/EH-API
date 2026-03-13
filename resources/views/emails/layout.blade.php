<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'EH Boutique Experience')</title>
    <style>
        .data-table td { padding: 8px 0; vertical-align: top; border-bottom: 1px solid #f0f0f0; font-family: Arial, Helvetica, sans-serif; font-size:14px; }
        .data-table tr:last-child td { border-bottom: none; }
        .data-table td:first-child { color: #888888; width: 140px; }
        .data-table td:last-child { color: #333333; }
        .highlight-label { font-size: 11px; font-weight: 700; letter-spacing: 1.5px; color: #888888; text-transform: uppercase; margin-bottom: 12px; }
        .highlight-sublabel { margin-top: 10px; font-size: 13px; color: #888888; }
        .alert-block { background: #f5f6f8; border-radius: 6px; padding: 14px 18px; margin: 20px 0; font-size: 14px; color: #555555; line-height: 1.6; }
        a { color: #3E4251; text-decoration: none; }
    </style>
</head>
<body bgcolor="#e8eaed" style="margin:0; padding:0; background-color:#e8eaed;">

    <table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#e8eaed" style="background-color:#e8eaed;">
        <tr>
            <td align="center" style="padding: 40px 20px 32px;">

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

                    {{-- Footer de contacto — overrideable: los mails internos lo omiten --}}
                    @section('card_footer')
                    <tr>
                        <td style="padding: 0 44px;">
                            <hr style="border:none; border-top:1px solid #eeeeee; margin:0;">
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 24px 44px 32px; font-family: Arial, Helvetica, sans-serif; font-size:13px; line-height:1.9; color:#666666;">
                            <p style="margin:0 0 8px;">Ante cualquier consulta, no dudes en contactarnos.</p>
                            <p style="margin:0;">
                                <strong style="color:#3E4251;">Contacto:</strong> <a href="mailto:info@ehboutiqueexperience.com" style="color:#3E4251; text-decoration:none;">info@ehboutiqueexperience.com</a><br>
                                <strong style="color:#3E4251;">WhatsApp:</strong> +54 9 2966 753463<br>
                                <strong style="color:#3E4251;">Teléfono:</strong> +54 9 2966 753463
                            </p>
                        </td>
                    </tr>
                    @show

                </table>

                {{-- Copy fuera del card --}}
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;">
                    <tr>
                        <td align="center" style="padding-top: 16px; font-family: Arial, Helvetica, sans-serif; font-size:12px; color:#9a9ea8; line-height:1.8;">
                            <a href="https://www.ehboutiqueexperience.com" target="_blank" style="color:#3E4251; text-decoration:none; display:block;">www.ehboutiqueexperience.com</a>
                            <span style="display:block;">Desarrollado por <a href="https://daptee.com.ar" target="_blank" style="color:#3E4251; text-decoration:none;">Daptee</a></span>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>

</body>
</html>

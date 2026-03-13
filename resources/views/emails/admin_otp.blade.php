@extends('emails.layout')

@section('title', 'Código de verificación de acceso')

@section('card_footer')
@endsection

@section('content')
    <p style="margin:0 0 14px;">Hola <strong style="color:#3E4251;">{{ $name }}</strong>,</p>
    <p style="margin:0 0 24px;">Ingresá el siguiente código para completar tu inicio de sesión:</p>

    {{-- Código OTP destacado --}}
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 24px;">
        <tr>
            <td align="center">
                <p style="margin:0 0 10px; font-size:11px; font-weight:700; letter-spacing:1.5px; color:#888888; text-transform:uppercase;">Tu código de verificación</p>
                <div style="display:inline-block; background:#f5f6f8; border:2px solid #3E4251; border-radius:6px; padding:14px 36px; font-size:36px; font-weight:700; font-family:'Courier New',Courier,monospace; color:#3E4251; letter-spacing:10px;">{{ $otp }}</div>
                <p style="margin:10px 0 0; font-size:13px; color:#888888;">Válido por <strong>10 minutos</strong>.</p>
            </td>
        </tr>
    </table>

    {{-- Alerta --}}
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td style="background:#f5f6f8; border-radius:6px; padding:14px 18px; font-size:14px; color:#555555; line-height:1.6;">
                Si no intentaste iniciar sesión, ignorá este correo. Tu cuenta permanece segura.
            </td>
        </tr>
    </table>
@endsection

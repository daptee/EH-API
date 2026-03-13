@extends('emails.layout')

@section('title', 'Restablecimiento de contraseña')

@section('card_footer')
@endsection

@section('content')
    <p style="margin:0 0 14px;">Hola <strong style="color:#3E4251;">{{ $data['name'] }}</strong>,</p>
    <p style="margin:0 0 14px;">Hemos generado una nueva contraseña temporal para tu cuenta. Usala para ingresar y luego cámbiala desde tu perfil.</p>
    <p style="margin:0 0 24px;"><strong>Cuenta:</strong> {{ $data['email'] }}</p>

    {{-- Contraseña destacada --}}
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 24px;">
        <tr>
            <td align="center">
                <p style="margin:0 0 10px; font-size:11px; font-weight:700; letter-spacing:1.5px; color:#888888; text-transform:uppercase;">Tu contraseña temporal</p>
                <div style="display:inline-block; background:#f5f6f8; border:2px solid #3E4251; border-radius:6px; padding:14px 36px; font-size:22px; font-weight:700; font-family:'Courier New',Courier,monospace; color:#3E4251; letter-spacing:4px;">{{ $data['password'] }}</div>
            </td>
        </tr>
    </table>

    {{-- Alerta --}}
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td style="background:#fff8e1; border-left:4px solid #f5a623; border-radius:4px; padding:12px 16px; font-size:14px; color:#7a5f00; line-height:1.6;">
                ⚠️ <strong>Importante:</strong> Si no solicitaste este cambio, por favor contactá a soporte de inmediato.
            </td>
        </tr>
    </table>
@endsection

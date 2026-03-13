@extends('emails.layout')

@section('title', 'Código de ingreso - EH Boutique Experience')

@section('content')
    <p style="margin:0 0 14px;">Hola <strong style="color:#3E4251;">{{ $data['name'] }}</strong>,</p>
    <p style="margin:0 0 24px;">Te informamos que el código para ingresar a tu suite es el siguiente:</p>

    {{-- Código destacado --}}
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 24px;">
        <tr>
            <td align="center">
                <p style="margin:0 0 10px; font-size:11px; font-weight:700; letter-spacing:1.5px; color:#888888; text-transform:uppercase;">Código de ingreso · Suite {{ $data['suite_name'] ?? $data['room_number'] }}</p>
                <div style="display:inline-block; background:#f5f6f8; border:2px solid #3E4251; border-radius:6px; padding:14px 36px; font-size:36px; font-weight:700; font-family:'Courier New',Courier,monospace; color:#3E4251; letter-spacing:10px;">{{ $data['code'] }}</div>
                <p style="margin:10px 0 0; font-size:13px; color:#888888;">Reserva N° <strong>{{ $data['reservation_number'] }}</strong></p>
            </td>
        </tr>
    </table>

    <p style="margin:0; font-size:14px; color:#666666;">Estamos a disposición para lo que necesites. ¡Bienvenido/a!</p>
@endsection

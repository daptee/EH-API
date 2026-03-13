@extends('emails.layout')

@section('title', 'Confirmación de reserva - EH Boutique Experience')

{{-- Footer solo para el huésped, no para la notificación interna --}}
@section('card_footer')
@if($type == "cliente")
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
@endif
@endsection

@section('content')
@if($type == "cliente")
    <p style="margin:0 0 14px;">Hola <strong style="color:#3E4251;">{{ $data['name'] }}</strong>,</p>
    <p style="margin:0 0 20px;">¡Gracias por confiar en nosotros! Esperamos que tengas una estadía increíble. A continuación encontrás el resumen de tu reserva:</p>
@else
    <p style="margin:0 0 20px;">Se ha recibido una nueva reserva. Los datos son los siguientes:</p>
@endif

    <table width="100%" cellpadding="0" cellspacing="0" border="0" class="data-table" style="margin:0 0 20px;">
        <tr>
            <td><strong>Reserva N°:</strong></td>
            <td>{{ $data['reservation_number'] }}</td>
        </tr>
        <tr>
            <td><strong>Check-in:</strong></td>
            <td>{{ $data['check_in'] }}</td>
        </tr>
        <tr>
            <td><strong>Check-out:</strong></td>
            <td>{{ $data['check_out'] }}</td>
        </tr>
        <tr>
            <td><strong>Habitación N°:</strong></td>
            <td>{{ $data['room_number'] }}</td>
        </tr>
        <tr>
            <td><strong>Pasajeros:</strong></td>
            <td>{{ $data['number_of_passengers'] }}</td>
        </tr>
@if($type != "cliente")
        <tr>
            <td><strong>Huésped:</strong></td>
            <td>{{ $data['name'] }} {{ $data['last_name'] }}</td>
        </tr>
        <tr>
            <td><strong>Email:</strong></td>
            <td>{{ $data['email'] }}</td>
        </tr>
        <tr>
            <td><strong>Teléfono:</strong></td>
            <td>{{ $data['phone'] }}</td>
        </tr>
@endif
    </table>

@if($type == "cliente")
    <p style="margin:0; font-size:14px; color:#666666;">Recordá que al llegar podés hacer el check-in virtual en el Totem que encontrarás en el hotel. ¡Hasta pronto!</p>
@endif
@endsection

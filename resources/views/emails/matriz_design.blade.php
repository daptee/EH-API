@extends('emails.layout')

@section('title', 'Nueva solicitud Cápsula Matriz - EH Boutique Experience')

@section('card_footer')
@endsection

@section('content')
    <p style="margin:0 0 20px;">Se registró una nueva solicitud de información correspondiente a la <strong>Cápsula Matriz</strong>.</p>

    <table width="100%" cellpadding="0" cellspacing="0" border="0" class="data-table" style="margin:0 0 20px;">
        <tr>
            <td><strong>Nombre:</strong></td>
            <td>{{ $data['name'] }} {{ $data['lastname'] }}</td>
        </tr>
        <tr>
            <td><strong>Habitación:</strong></td>
            <td>{{ $data['room_number'] }}</td>
        </tr>
        <tr>
            <td><strong>Reserva N°:</strong></td>
            <td>{{ $data['reservation_number'] }}</td>
        </tr>
        <tr>
            <td><strong>Email:</strong></td>
            <td>{{ $data['email'] }}</td>
        </tr>
        <tr>
            <td><strong>Teléfono:</strong></td>
            <td>{{ $data['phone'] }}</td>
        </tr>
    </table>

    <p style="margin:0 0 8px; font-size:14px; color:#555555;"><strong>Mensaje:</strong></p>
    <p style="margin:0; font-size:14px; color:#444444; background:#f5f6f8; border-radius:6px; padding:14px 18px; line-height:1.6;">{{ $data['text'] }}</p>
@endsection

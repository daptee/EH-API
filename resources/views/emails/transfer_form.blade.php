@extends('emails.layout')

@section('title', 'EH Boutique Experience - Solicitud de información de traslado')

{{-- Mail interno: sin footer de contacto --}}
@section('card_footer')
@endsection

@section('content')
    <p style="margin:0 0 20px;">Hola, se ha recibido una solicitud de información correspondiente al traslado <strong>{{ $data['transfer_name'] }}</strong>.</p>

    <table width="100%" cellpadding="0" cellspacing="0" border="0" class="data-table">
        <tr>
            <td><strong>Nombre:</strong></td>
            <td>{{ $data['name'] }} {{ $data['lastname'] }}</td>
        </tr>
        <tr>
            <td><strong>Email:</strong></td>
            <td>{{ $data['email'] }}</td>
        </tr>
        <tr>
            <td><strong>Teléfono:</strong></td>
            <td>{{ $data['phone'] }}</td>
        </tr>
        <tr>
            <td><strong>Traslado:</strong></td>
            <td>{{ $data['transfer_name'] }}</td>
        </tr>
        <tr>
            <td><strong>Mensaje:</strong></td>
            <td>{{ $data['message'] }}</td>
        </tr>
    </table>

    <p style="margin:16px 0 0; font-size:14px; color:#888888;">No tardes en responderle.</p>
@endsection

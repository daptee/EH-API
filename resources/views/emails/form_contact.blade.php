@extends('emails.layout')

@section('title', 'EH Boutique Experience - Nueva consulta web')

@section('content')
    <p>Hola,</p>
    <p>Se recibió una nueva consulta a través del formulario de contacto de la web.</p>

    <table class="data-table">
        <tr>
            <td>Nombre</td>
            <td>{{ $data['name'] }}</td>
        </tr>
        <tr>
            <td>Email</td>
            <td>{{ $data['email'] }}</td>
        </tr>
        <tr>
            <td>Mensaje</td>
            <td>{{ $data['message'] }}</td>
        </tr>
    </table>

    <p>No tardes en responderle.</p>
@endsection

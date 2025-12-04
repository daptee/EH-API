<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Confirmación reserva</title>
</head>

<body>
    <p>
        @if($type == "cliente")
        Hola {{ $data['name'] }}, <br>
        Queremos agradecerte por haber confiado en nosotros! Esperamos tengas una estadía confortable. Te dejamos a continuacion un resumen de tu reserva: <br>
        @elseif($type == "agencia")
        Hola {{ $data['agency_user_name'] }}, <br>
        Se deja a continuacion un resumen de la reserva generada por el sistema de agencias de EH. Los datos de la misma son: <br>
        @else
        Hola, <br>
        Se ha recibido una nueva reserva proveniente de la web de agencias. Los datos de la misma son: <br>
        @endif
        Nombre y apellido: {{ $data['name'] . ' ' . $data['last_name'] }} <br>
        Telefono: {{ $data['phone'] }} <br>
        Email: {{ $data['email'] }} <br>
        Check-in: {{ $data['check_in'] }} <br>
        Check-out: {{ $data['check_out'] }} <br>
        Cantidad de pasajeros: {{ $data['number_of_passengers'] }} <br>
        Nro de habitación: {{ $data['room_number'] }} <br>

        @if($type == "cliente")
        <br>
        Recorda cuando llegues hacer el checkin virtual en el Totem que encontrarás en el hotel. <br>
        Ante cualquier problema, podes contactarte con nosotros al WhatsApp: +54 9 2966 753463 o a nuestro correo electrónico info@ehboutiqueexperience.com. <br>
        Muchas gracias y buena estadía!
        @endif
        Muchas gracias <br>
        El equipo IT de EH Boutique Experience.
    </p>
</body>

</html>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>EH Boutique experience</title>
</head>

<body>
    <p>
        Hola! Te informamos que se registro una nueva solicitd de informacion correspondiente a la capsula Matriz. <br>
        La misma la hizo {{ $data['name'] . ' ' . $data['lastname'] }}, correspondiente a huesped de la habitacion nro {{ $data['room_number'] }} de la reserva {{ $data['reservation_number'] }}. <br>
        Los datos de contacto son: <br>
        <br>
        Email: {{ $data['email'] }} <br>
        Telefono: {{ $data['phone'] }} <br>
        <br>
        El mensaje que ingreso es: <br>
        <br>
        {{ $data['text'] }} <br>
        <br>
        No demores en contactarlo. <br>
        El equipo de IT de EH
    </p>
</body>

</html>
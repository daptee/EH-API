<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>EH Boutique experience - Codigo ingreso</title>
</head>
<body>
    <p>
        Hola {{ $data['name'] }}. <br>
        <br>
        Te informamos que el codigo para ingresar a la habitacion numero {{ $data['room_number'] }} es: <br>
        <br>
        <strong style="display: block; text-align: center; font-size: 1.2em;">{{ $data['code'] }}</strong> <br>
        <br>
        Esto corresponde a su reserva nro: {{ $data['reservation_number'] }}. <br>
        <br>
        Ante cualquier duda, comuniquese al +54 9 2966 38-5468 <br>
        Muchas gracias. <br>
        El equipo de EH boutique experience.
    </p>
</body>
</html>
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
        Hola {{ $data['name'] }}, <br>
        @if($type == "cliente")
        Queremos agradecerte por haber confiado en nosotros! Esperamos tengas una estadía confortable. Te dejamos a continuacion un resumen de tu reserva: <br>
        @else
        Se ha recibido una nueva reserva proveniente de la web. Los datos de la misma son: <br>
        @endif
        Check-in: {{ $data['check_in'] }} <br>
        Check-out: {{ $data['check_out'] }} <br>
        Cantidad de pasajeros: {{ $data['number_of_passengers'] }} <br>
        Datos de contacto: {{ $data['name'] }} - {{ $data['email'] }} <br> <br>
    
        @if($type == "cliente")
        Recorda cuando llegues hacer el checkin virtual en el Totem que encontrarás en el hotel. <br>
        Muchas gracias y buena estadía!
        @else
        Muchas gracias <br>
        El equipo IT de EH Boutique Experience.
        @endif
    </p>
</body>
</html>
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
        Buenas tardes {{ $data['name'] . ' ' . $data['last_name'] }}, <br>
        <br>
        Le informamos que el código para ingresar a su SUITE {{ $data['suite_name'] ?? $data['room_number'] }} es: <br>
        <br>
        <strong style="display: block; text-align: center; font-size: 1.2em;">{{ $data['code'] }}</strong> <br>
        <br>
        Esto corresponde a su reserva num: {{ $data['reservation_number'] }}. <br>
        <br>
        Muchas gracias por elegirnos! <br>
        <br>
        No dude en contactarse con nosotros en caso de necesitarlo al +54 9 2966 38-5468 <br>
        <br>
        Estamos a disposición para lo que necesite. <br>
        <br>
        Equipo EHBE.
    </p>
</body>
</html>
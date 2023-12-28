<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>EH Boutique experience - Consulta web</title>
</head>
<body>
    <p>
        Hola. Te informamos que han enviado una nueva consulta por la web. <br>
        Lo mando <br>
        <br>
        Nombre: {{ $data['name'] }} <br>
        Email: {{ $data['email'] }} <br>
        <br>
        y dijo lo siguiente: <br>
        <br>
        {{ $data['message'] }} <br>
        <br>
        No tardes en responderle! <br>
        El equipo de EH
    </p>
</body>
</html>
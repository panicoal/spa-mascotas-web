<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verificar Cuenta</title>
</head>
<body>

    <h2>Bienvenido a Pet Spa</h2>

    <p>
        Hola {{ $user->nombre_completo }}
    </p>

    <p>
        Debes verificar tu cuenta para activar el acceso.
    </p>

    <p>
        Este enlace expira en 15 minutos.
    </p>

    <a href="{{ $verificationUrl }}">
        Verificar Cuenta
    </a>

</body>
</html>
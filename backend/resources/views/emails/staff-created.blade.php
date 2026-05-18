<!DOCTYPE html>
<html>
<head>
    <title>Credenciales de acceso</title>
</head>
<body>

    <h2>Bienvenido a Pet Spa</h2>

    <p>Tu cuenta ha sido creada por el administrador.</p>

    <p><strong>Email:</strong> {{ $user->email }}</p>

    <p><strong>Contraseña temporal:</strong> {{ $password }}</p>

    <p>Por seguridad, cambia tu contraseña al iniciar sesión.</p>

</body>
</html>
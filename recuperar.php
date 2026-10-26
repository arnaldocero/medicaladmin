<?php
// Página pública: pedir el enlace para restablecer la contraseña
require_once "includes/sesion.php";
iniciarSesion();
if (empty($_SESSION["csrf_publico"])) $_SESSION["csrf_publico"] = bin2hex(random_bytes(32));
?>
<!doctype html>
<html class="no-js" lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recuperar contraseña | Administrador de Membresías</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
</head>
<body class="bg-dark">
    <div class="sufee-login d-flex align-content-center flex-wrap">
        <div class="container">
            <div class="login-content">
                <div class="login-form">
                    <h5 class="mb-3">Recuperar contraseña</h5>
                    <p class="small text-muted">Escribe el correo de tu cuenta y te enviaremos un enlace para crear una contraseña nueva.</p>
                    <form id="formSolicitar">
                        <input type="hidden" name="csrf" value="<?php echo $_SESSION["csrf_publico"]; ?>">
                        <div class="form-group">
                            <label for="email">Correo electrónico</label>
                            <input type="email" id="email" name="email" class="form-control" placeholder="Email" required autofocus>
                        </div>
                        <button type="submit" class="btn btn-success btn-flat m-b-15 m-t-15 btn-block">Enviar enlace</button>
                        <div class="text-center mt-3"><a href="login.php" class="small">Volver al inicio de sesión</a></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="assets/js/recuperar.js?v=<?php echo filemtime(__DIR__ . "/assets/js/recuperar.js"); ?>"></script>
</body>
</html>

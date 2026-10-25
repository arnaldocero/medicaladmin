<!doctype html>
<html class="no-js" lang="es">
<head>
    <meta charset="utf-8">
    <title>Login | Administrador de Membresías</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
</head>
<body class="bg-dark">
    <div class="sufee-login d-flex align-content-center flex-wrap">
        <div class="container">
            <div class="login-content">
                <div class="login-form">
                    <?php
                    // Aviso cuando verificarSesion() cerró la sesión
                    $avisos = [
                        "inactividad" => "Tu sesión se cerró por inactividad. Inicia sesión de nuevo.",
                        "desactivado" => "Tu usuario fue desactivado. Contacta a un superadmin.",
                    ];
                    if (isset($avisos[$_GET["motivo"] ?? ""])): ?>
                    <div class="alert alert-warning"><?php echo $avisos[$_GET["motivo"]]; ?></div>
                    <?php endif; ?>
                    <form id="formLogin">
                        <div class="form-group">
                            <label>Correo Electrónico</label>
                            <input type="email" id="email" class="form-control" placeholder="Email" required>
                        </div>
                        <div class="form-group">
                            <label>Contraseña</label>
                            <input type="password" id="password" class="form-control" placeholder="Password" required>
                        </div>
                        <button type="submit" class="btn btn-success btn-flat m-b-30 m-t-30">Entrar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="assets/js/login.js?v=<?php echo filemtime(__DIR__ . "/assets/js/login.js"); ?>"></script> </body>
</html>
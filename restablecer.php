<?php
// Página pública: crear una contraseña nueva con el enlace recibido por email
require_once "includes/sesion.php";
iniciarSesion();
if (empty($_SESSION["csrf_publico"])) $_SESSION["csrf_publico"] = bin2hex(random_bytes(32));

// Se comprueba el enlace al abrir la página para no pedir una contraseña que luego no servirá
$token = (string)($_GET["token"] ?? "");
$valido = false;
if (preg_match('/^[0-9a-f]{64}$/', $token)) {
    try {
        $stmt = Conexion::conectar()->prepare("SELECT COUNT(*) FROM recuperacion_password r
            INNER JOIN usuarios_admin u ON u.id = r.usuario_id AND u.estado = 1
            WHERE r.token_hash = :h AND r.usado = 0 AND r.expira > NOW()");
        $stmt->execute([":h" => hash('sha256', $token)]);
        $valido = (bool)$stmt->fetchColumn();
    } catch (Exception $e) {
        error_log("restablecer: " . $e->getMessage());
    }
}
// Que el token no viaje a otros sitios en la cabecera Referer
header("Referrer-Policy: no-referrer");
?>
<!doctype html>
<html class="no-js" lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nueva contraseña | Administrador de Membresías</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
</head>
<body class="bg-dark">
    <div class="sufee-login d-flex align-content-center flex-wrap">
        <div class="container">
            <div class="login-content">
                <div class="login-form">
                    <h5 class="mb-3">Crear contraseña nueva</h5>
                    <?php if (!$valido): ?>
                        <div class="alert alert-warning">El enlace no es válido o ya venció. Los enlaces duran 60 minutos y solo se pueden usar una vez.</div>
                        <a href="recuperar.php" class="btn btn-success btn-block">Solicitar un enlace nuevo</a>
                        <div class="text-center mt-3"><a href="login.php" class="small">Volver al inicio de sesión</a></div>
                    <?php else: ?>
                    <form id="formRestablecer" autocomplete="off">
                        <input type="hidden" name="csrf" value="<?php echo $_SESSION["csrf_publico"]; ?>">
                        <input type="hidden" name="token" value="<?php echo $token; ?>">
                        <div class="form-group">
                            <label for="password_nueva">Nueva contraseña</label>
                            <input type="password" id="password_nueva" name="password_nueva" class="form-control" minlength="8" autocomplete="new-password" required autofocus>
                            <small class="text-muted">Mínimo 8 caracteres.</small>
                        </div>
                        <div class="form-group">
                            <label for="password_confirmar">Confirmar contraseña</label>
                            <input type="password" id="password_confirmar" name="password_confirmar" class="form-control" minlength="8" autocomplete="new-password" required>
                        </div>
                        <button type="submit" class="btn btn-success btn-flat m-t-15 btn-block">Guardar contraseña</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="assets/js/recuperar.js?v=<?php echo filemtime(__DIR__ . "/assets/js/recuperar.js"); ?>"></script>
</body>
</html>

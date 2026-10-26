<?php
// Recuperación de contraseña (páginas públicas recuperar.php y restablecer.php).
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once "../includes/sesion.php";
require_once "../includes/validacion.php";
require_once "../includes/bitacora.php";
require_once "../includes/correo.php";

const MINUTOS_VALIDEZ_ENLACE = 60;
const MAX_SOLICITUDES_EMAIL = 3;   // por email y hora
const MAX_SOLICITUDES_IP = 10;     // por IP y hora

iniciarSesion();

// Las páginas públicas guardan en la sesión un token CSRF propio ("csrf_publico")
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['csrf_publico'])
    || !hash_equals($_SESSION['csrf_publico'], (string)($_POST['csrf'] ?? ''))) {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Solicitud no válida. Recarga la página e inténtalo de nuevo."]);
    exit;
}

$action = $_POST['action'] ?? '';
$ip = $_SERVER['REMOTE_ADDR'] ?? '';

try {
    $db = Conexion::conectar();

    if ($action == 'solicitar') {
        $email = emailPost('email', 'El email');
        // Respuesta siempre igual, exista o no el email, para no revelar qué cuentas existen
        $respuesta = ["status" => "success", "message" => "Si el correo pertenece a una cuenta activa, en unos minutos recibirás un enlace para restablecer la contraseña. Revisa también la carpeta de spam."];

        $stmt = $db->prepare("SELECT
                (SELECT COUNT(*) FROM recuperacion_password WHERE email = :e AND fecha > NOW() - INTERVAL 1 HOUR) AS por_email,
                (SELECT COUNT(*) FROM recuperacion_password WHERE ip = :ip AND fecha > NOW() - INTERVAL 1 HOUR) AS por_ip");
        $stmt->execute([":e" => $email, ":ip" => $ip]);
        $cuenta = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($cuenta["por_email"] >= MAX_SOLICITUDES_EMAIL || $cuenta["por_ip"] >= MAX_SOLICITUDES_IP) {
            http_response_code(429);
            echo json_encode(["status" => "error", "message" => "Demasiadas solicitudes. Espera una hora e inténtalo de nuevo."]);
            exit;
        }

        $stmt = $db->prepare("SELECT id, nombre FROM usuarios_admin WHERE email = :e AND estado = 1");
        $stmt->execute([":e" => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            // Se registra igual para que cuente en el límite
            $db->prepare("INSERT INTO recuperacion_password (email, ip) VALUES (:e, :ip)")->execute([":e" => $email, ":ip" => $ip]);
            echo json_encode($respuesta);
            exit;
        }

        if (urlBase() === '') {
            error_log("recuperar_ajax: falta 'url_base' en config.php, no se puede enviar el enlace");
            echo json_encode($respuesta);
            exit;
        }

        // Solo el último enlace sirve
        $db->prepare("UPDATE recuperacion_password SET usado = 1 WHERE usuario_id = :id AND usado = 0")->execute([":id" => $usuario["id"]]);

        $token = bin2hex(random_bytes(32));
        $db->prepare("INSERT INTO recuperacion_password (usuario_id, email, token_hash, ip, expira)
                      VALUES (:id, :e, :h, :ip, NOW() + INTERVAL " . MINUTOS_VALIDEZ_ENLACE . " MINUTE)")
           ->execute([":id" => $usuario["id"], ":e" => $email, ":h" => hash('sha256', $token), ":ip" => $ip]);

        $enlace = urlBase() . "/restablecer.php?token=" . $token;
        $enlaceHtml = htmlspecialchars($enlace, ENT_QUOTES, 'UTF-8');
        $nombreHtml = htmlspecialchars($usuario["nombre"], ENT_QUOTES, 'UTF-8');
        enviarCorreo($email, "Restablecer tu contraseña", "
            <p>Hola $nombreHtml,</p>
            <p>Recibimos una solicitud para restablecer la contraseña de tu cuenta del panel administrativo.</p>
            <p style=\"margin:24px 0;\"><a href=\"$enlaceHtml\" style=\"background:#28a745;color:#fff;padding:10px 18px;border-radius:4px;text-decoration:none;\">Restablecer contraseña</a></p>
            <p style=\"font-size:13px;color:#666;\">El enlace vence en " . MINUTOS_VALIDEZ_ENLACE . " minutos y solo se puede usar una vez.
               Si no fuiste tú, ignora este correo: tu contraseña no cambia.</p>
            <p style=\"font-size:12px;color:#999;word-break:break-all;\">Si el botón no funciona, copia este enlace: $enlaceHtml</p>");

        echo json_encode($respuesta);
        exit;
    }

    if ($action == 'restablecer') {
        $token = (string)($_POST['token'] ?? '');
        $nueva = passwordPost('password_nueva', true);
        if ($nueva !== (string)($_POST['password_confirmar'] ?? '')) errorValidacion("La confirmación no coincide con la nueva contraseña");

        $stmt = $db->prepare("SELECT r.id, u.id AS usuario_id, u.nombre, u.email
            FROM recuperacion_password r
            INNER JOIN usuarios_admin u ON u.id = r.usuario_id AND u.estado = 1
            WHERE r.token_hash = :h AND r.usado = 0 AND r.expira > NOW()");
        $stmt->execute([":h" => hash('sha256', $token)]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$fila) errorValidacion("El enlace no es válido o ya venció. Solicita uno nuevo.");

        $db->prepare("UPDATE usuarios_admin SET password = :p WHERE id = :id")
           ->execute([":p" => password_hash($nueva, PASSWORD_DEFAULT), ":id" => $fila["usuario_id"]]);
        $db->prepare("UPDATE recuperacion_password SET usado = 1 WHERE usuario_id = :id")->execute([":id" => $fila["usuario_id"]]);
        // Desbloquea el login si estaba bloqueado por intentos fallidos
        $db->prepare("DELETE FROM login_intentos_admin WHERE email = :e AND exitoso = 0")->execute([":e" => $fila["email"]]);

        registrarBitacora($db, "editar", "usuario_admin", (int)$fila["usuario_id"], "Restableció su contraseña con el enlace enviado por email",
            ["password" => ["antes" => null, "despues" => "(contraseña cambiada)"]],
            ["id" => (int)$fila["usuario_id"], "nombre" => $fila["nombre"]]);

        unset($_SESSION['csrf_publico']);
        echo json_encode(["status" => "success"]);
        exit;
    }

    echo json_encode(["status" => "error", "message" => "Acción no válida"]);
} catch (Exception $e) {
    error_log("recuperar_ajax: " . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "No se pudo procesar la solicitud. Inténtalo más tarde."]);
}

<?php
// Manejo central de la sesión del panel: cookie segura, inactividad y revalidación del usuario.
require_once __DIR__ . "/../class/Conexion.php";

const MINUTOS_INACTIVIDAD = 30;
const ROLES_PANEL = ["superadmin", "admin"];

// Inicia la sesión con una cookie segura (igual en todas las páginas y endpoints)
function iniciarSesion() {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    // Que PHP no borre el archivo de sesión antes del tiempo de inactividad
    ini_set('session.gc_maxlifetime', (string)(MINUTOS_INACTIVIDAD * 60 + 600));
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => $https, 'httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

// Borra la sesión y su cookie
function cerrarSesion() {
    iniciarSesion();
    $_SESSION = [];
    $p = session_get_cookie_params();
    setcookie(session_name(), '', ['expires' => time() - 3600, 'path' => $p['path'], 'secure' => $p['secure'], 'httponly' => true, 'samesite' => 'Lax']);
    session_destroy();
}

// Exige una sesión válida y uno de los roles indicados.
// - Sin sesión, sesión inactiva o usuario desactivado: cierra la sesión y manda al login (401 en AJAX).
// - Rol sin permiso: vuelve al dashboard (403 en AJAX).
// El rol y el nombre se leen de la BD en cada petición, así un cambio de rol se aplica al momento.
function verificarSesion(array $roles = ROLES_PANEL, $ajax = false) {
    iniciarSesion();

    $motivo = null;
    if (($_SESSION["validar"] ?? '') !== "ok" || empty($_SESSION["id"])) {
        $motivo = "sin_sesion";
    } elseif (time() - ($_SESSION["ultima_actividad"] ?? 0) > MINUTOS_INACTIVIDAD * 60) {
        $motivo = "inactividad";
    } else {
        $stmt = Conexion::conectar()->prepare("SELECT nombre, rol FROM usuarios_admin WHERE id = :id AND estado = 1");
        $stmt->execute([":id" => $_SESSION["id"]]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$usuario) {
            $motivo = "desactivado";
        } else {
            $_SESSION["rol"] = $usuario["rol"];
            $_SESSION["nombre"] = $usuario["nombre"];
        }
    }

    if ($motivo !== null) {
        cerrarSesion();
        if ($ajax) {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Tu sesión terminó. Inicia sesión de nuevo.", "sesion" => $motivo]);
        } else {
            header("location: login.php" . ($motivo === "sin_sesion" ? "" : "?motivo=" . $motivo));
        }
        exit;
    }

    $_SESSION["ultima_actividad"] = time();

    if (!in_array($_SESSION["rol"], $roles, true)) {
        if ($ajax) {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "No tienes permisos para esta acción"]);
        } else {
            header("location: index.php");
        }
        exit;
    }

    // Token CSRF de la sesión: utils.js lo envía en cada petición AJAX
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }
}

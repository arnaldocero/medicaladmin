<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once "../includes/sesion.php";
require_once "../includes/csrf.php";
require_once "../includes/validacion.php";
require_once "../includes/bitacora.php";

// Cualquier usuario del panel, sobre su propia cuenta
verificarSesion(ROLES_PANEL, true);
verificarCsrf();

$action = $_POST['action'] ?? '';
$miId = (int)$_SESSION["id"];

try {
    $db = Conexion::conectar();

    if ($action == 'guardar_nombre') {
        $nombre = textoPost('nombre', 'El nombre', 100);
        $stmt = $db->prepare("SELECT nombre FROM usuarios_admin WHERE id = :id");
        $stmt->execute([":id" => $miId]);
        $anterior = $stmt->fetchColumn();

        if ($anterior !== $nombre) {
            $db->prepare("UPDATE usuarios_admin SET nombre = :n WHERE id = :id")->execute([":n" => $nombre, ":id" => $miId]);
            $_SESSION["nombre"] = $nombre;
            registrarBitacora($db, "editar", "usuario_admin", $miId, "Cambió su nombre en Mi perfil",
                ["nombre" => ["antes" => $anterior, "despues" => $nombre]]);
        }
        echo json_encode(["status" => "success"]);
        exit;
    }

    if ($action == 'cambiar_password') {
        $actual = (string)($_POST['password_actual'] ?? '');
        $nueva = passwordPost('password_nueva', true);
        if ($nueva !== (string)($_POST['password_confirmar'] ?? '')) errorValidacion("La confirmación no coincide con la nueva contraseña");
        if ($nueva === $actual) errorValidacion("La nueva contraseña debe ser distinta de la actual");

        $stmt = $db->prepare("SELECT password FROM usuarios_admin WHERE id = :id AND estado = 1");
        $stmt->execute([":id" => $miId]);
        if (!password_verify($actual, (string)$stmt->fetchColumn())) {
            errorValidacion("La contraseña actual no es correcta");
        }

        $db->prepare("UPDATE usuarios_admin SET password = :p WHERE id = :id")
           ->execute([":p" => password_hash($nueva, PASSWORD_DEFAULT), ":id" => $miId]);
        // Los enlaces de recuperación pendientes dejan de servir
        try {
            $db->prepare("UPDATE recuperacion_password SET usado = 1 WHERE usuario_id = :id AND usado = 0")->execute([":id" => $miId]);
        } catch (Exception $e) { /* tabla aún no creada */ }

        // Nuevo ID de sesión tras cambiar la contraseña
        session_regenerate_id(true);
        registrarBitacora($db, "editar", "usuario_admin", $miId, "Cambió su contraseña en Mi perfil",
            ["password" => ["antes" => null, "despues" => "(contraseña cambiada)"]]);
        echo json_encode(["status" => "success"]);
        exit;
    }

    echo json_encode(["status" => "error", "message" => "Acción no válida"]);
} catch (Exception $e) {
    error_log("perfil_ajax: " . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Error al procesar la solicitud"]);
}

<?php
// Evitar que cualquier error de PHP se imprima como HTML y rompa el JSON
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once "../class/Conexion.php";
require_once "../includes/csrf.php";
require_once "../includes/validacion.php";

// Encabezado obligatorio para JSON
header('Content-Type: application/json');

if(!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "superadmin"){
    echo json_encode(["status" => "error", "message" => "No tienes permisos"]);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Todo lo que no sea listar modifica o lee datos concretos: solo por POST con token CSRF
if ($action !== 'listar') {
    verificarCsrf();
}

try {
    $db = Conexion::conectar();

    if ($action == 'listar') {
        $stmt = $db->prepare("SELECT id, nombre, email, rol FROM usuarios_admin WHERE estado = 1");
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    if ($action == 'guardar') {
        $id = idPost('idUsuario');
        $nombre = textoPost('nombre', 'El nombre', 100);
        $email = emailPost('email', 'El email');
        $rol = $_POST['rol'] ?? '';
        if (!in_array($rol, ['superadmin', 'admin'], true)) errorValidacion("Rol no válido");
        $password = passwordPost('password', $id === null);

        // Un superadmin no puede quitarse a sí mismo el rol (se quedaría sin acceso)
        if ($id === (int)$_SESSION['id'] && $rol !== 'superadmin') {
            errorValidacion("No puedes quitarte tu propio rol de superadmin");
        }

        if ($id !== null) {
            if ($password !== '') {
                $stmt = $db->prepare("UPDATE usuarios_admin SET nombre=:n, email=:e, rol=:r, password=:p WHERE id=:id");
                $stmt->bindValue(":p", password_hash($password, PASSWORD_DEFAULT));
            } else {
                $stmt = $db->prepare("UPDATE usuarios_admin SET nombre=:n, email=:e, rol=:r WHERE id=:id");
            }
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        } else {
            $stmt = $db->prepare("INSERT INTO usuarios_admin (nombre, email, password, rol, estado) VALUES (:n, :e, :p, :r, 1)");
            $stmt->bindValue(":p", password_hash($password, PASSWORD_DEFAULT));
        }

        $stmt->bindValue(":n", $nombre);
        $stmt->bindValue(":e", $email);
        $stmt->bindValue(":r", $rol);

        try {
            $stmt->execute();
        } catch (PDOException $e) {
            if (esDuplicado($e)) errorValidacion("Ya existe un usuario con ese email (puede estar inactivo)");
            throw $e;
        }
        echo json_encode(["status" => "success"]);
        exit;
    }

    if ($action == 'eliminar') {
        $id = idPost('id');
        if ($id === null) errorValidacion("Identificador no válido");
        if ($id === (int)$_SESSION['id']) errorValidacion("No puedes eliminar tu propio usuario");

        $stmt = $db->prepare("UPDATE usuarios_admin SET estado = 0 WHERE id = :id");
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        echo json_encode(["status" => "success"]);
        exit;
    }

    if ($action == 'obtener_usuario') {
        $stmt = $db->prepare("SELECT id, nombre, email, rol FROM usuarios_admin WHERE id = :id");
        $stmt->bindValue(":id", idPost('id'), PDO::PARAM_INT);
        $stmt->execute();
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        exit;
    }

    echo json_encode(["status" => "error", "message" => "Acción no válida"]);
} catch (Exception $e) {
    error_log("usuarios_ajax: " . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Error al procesar la solicitud"]);
}

<?php
// Evitar que cualquier error de PHP se imprima como HTML y rompa el JSON
error_reporting(0); 
ini_set('display_errors', 0);

session_start();
require_once "../class/Conexion.php";
require_once "../includes/csrf.php";

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
        $nombre = $_POST['nombre'];
        $email = $_POST['email'];
        $rol = $_POST['rol'];
        $id = $_POST['idUsuario'];

        if (!empty($id)) {
            if (!empty($_POST['password'])) {
                $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE usuarios_admin SET nombre=:n, email=:e, rol=:r, password=:p WHERE id=:id");
                $stmt->bindParam(":p", $pass);
            } else {
                $stmt = $db->prepare("UPDATE usuarios_admin SET nombre=:n, email=:e, rol=:r WHERE id=:id");
            }
            $stmt->bindParam(":id", $id);
        } else {
            $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO usuarios_admin (nombre, email, password, rol, estado) VALUES (:n, :e, :p, :r, 1)");
            $stmt->bindParam(":p", $pass);
        }

        $stmt->bindParam(":n", $nombre);
        $stmt->bindParam(":e", $email);
        $stmt->bindParam(":r", $rol);
        
        if ($stmt->execute()) {
            // Limpiamos cualquier salida previa accidental
            ob_clean(); 
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error en BD"]);
        }
        exit;
    }

    if ($action == 'eliminar') {
        $id = $_POST['id'];
        $stmt = $db->prepare("UPDATE usuarios_admin SET estado = 0 WHERE id = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        echo json_encode(["status" => "success"]);
        exit;
    }

    if ($action == 'obtener_usuario') {
        $id = $_POST['id'];
        $stmt = $db->prepare("SELECT id, nombre, email, rol FROM usuarios_admin WHERE id = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        exit;
    }

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    exit;
}
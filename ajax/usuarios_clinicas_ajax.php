<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');
session_start();
require_once "../class/Conexion.php";

// Solo personal del panel con sesión iniciada
if (!isset($_SESSION["validar"]) || $_SESSION["validar"] !== "ok"
    || !in_array($_SESSION["rol"] ?? '', ["superadmin", "admin"], true)) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "No autorizado"]);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
try {
    $db = Conexion::conectar();

    if ($action == 'listar_global') {
        $sql = "SELECT u.id, u.nombre, u.usuario, u.email, r.nombre_rol as rol, c.nombre_clinica
                FROM usuarios_clinicas u
                INNER JOIN roles_clinicas r ON u.rol_id = r.id
                INNER JOIN clientes_clinicas c ON u.clinica_id = c.id
                WHERE u.estado = 1 ORDER BY c.nombre_clinica ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    if ($action == 'guardar') {
        // Las peticiones de escritura solo se aceptan por POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(["status" => "error", "message" => "Método no permitido"]);
            exit;
        }

        $id = $_POST['idUserC'] ?? '';
        $pass = $_POST['password'] ?? '';
        $params = [
            ":idC" => $_POST['clinica_id_hidden'] ?? '',
            ":nom" => trim($_POST['nombre_uc'] ?? ''),
            ":usr" => trim($_POST['usuario_uc'] ?? ''),
            ":ema" => trim($_POST['email_uc'] ?? ''),
            ":rol_id" => $_POST['rol_id'] ?? ''
        ];

        if ($params[":idC"] === '' || $params[":nom"] === '' || $params[":usr"] === '' || $params[":rol_id"] === '') {
            echo json_encode(["status" => "error", "message" => "Clínica, nombre, usuario y rol son obligatorios"]);
            exit;
        }

        if (!empty($id)) {
            $sql = "UPDATE usuarios_clinicas SET clinica_id=:idC, nombre=:nom, usuario=:usr, email=:ema, rol_id=:rol_id WHERE id=:id";
            if(!empty($pass)){
                $sql = "UPDATE usuarios_clinicas SET clinica_id=:idC, nombre=:nom, usuario=:usr, email=:ema, rol_id=:rol_id, password=:pass WHERE id=:id";
                $params[":pass"] = password_hash($pass, PASSWORD_DEFAULT);
            }
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        } else {
            if (empty($pass)) {
                echo json_encode(["status" => "error", "message" => "La contraseña es obligatoria para un usuario nuevo"]);
                exit;
            }
            $sql = "INSERT INTO usuarios_clinicas (clinica_id, nombre, usuario, email, password, rol_id, estado)
                    VALUES (:idC, :nom, :usr, :ema, :pass, :rol_id, 1)";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":pass", password_hash($pass, PASSWORD_DEFAULT));
        }

        foreach($params as $k => $v) $stmt->bindValue($k, $v);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error al guardar"]);
        }
        exit;
    }

    if ($action == 'obtener') {
        // Sin la columna password: el hash nunca debe llegar al navegador
        $stmt = $db->prepare("SELECT id, clinica_id, nombre, usuario, email, rol_id FROM usuarios_clinicas WHERE id = :id");
        $stmt->bindValue(":id", $_POST['id'] ?? 0, PDO::PARAM_INT);
        $stmt->execute();
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        exit;
    }

    echo json_encode(["status" => "error", "message" => "Acción no válida"]);
} catch (Exception $e) {
    error_log("usuarios_clinicas_ajax: " . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Error al procesar la solicitud"]);
}

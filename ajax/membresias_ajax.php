<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

session_start();
require_once "../class/Conexion.php";
require_once "../includes/csrf.php";

if(!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "superadmin"){
    echo json_encode(["status" => "error", "message" => "No autorizado"]);
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
        $stmt = $db->prepare("SELECT * FROM clientes_clinicas WHERE estado = 1 ORDER BY id DESC");
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    if ($action == 'guardar') {
        $id = $_POST['idCliente'] ?? '';
        
        $datos = [
            ":nit" => $_POST['nit_empresa'],
            ":nom" => $_POST['nombre_clinica'],
            ":rep" => $_POST['representante_legal'],
            ":tel" => $_POST['telefono'],
            ":ema" => $_POST['email_contacto'],
            ":pla" => $_POST['plan_contratado'],
            ":f_a" => $_POST['fecha_activacion'],
            ":f_v" => $_POST['fecha_vencimiento']
        ];

        if (!empty($id)) {
            // EDITAR
            $sql = "UPDATE clientes_clinicas SET nit_empresa=:nit, nombre_clinica=:nom, representante_legal=:rep, 
                    telefono=:tel, email_contacto=:ema, plan_contratado=:pla, fecha_activacion=:f_a, 
                    fecha_vencimiento=:f_v WHERE id=:id";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":id", $id);
        } else {
            // NUEVO
            $sql = "INSERT INTO clientes_clinicas (nit_empresa, nombre_clinica, representante_legal, telefono, 
                    email_contacto, plan_contratado, fecha_activacion, fecha_vencimiento, estado) 
                    VALUES (:nit, :nom, :rep, :tel, :ema, :pla, :f_a, :f_v, 1)";
            $stmt = $db->prepare($sql);
        }

        foreach ($datos as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error al guardar"]);
        }
        exit;
    }

    if ($action == 'obtener') {
        $id = $_POST['id'];
        $stmt = $db->prepare("SELECT * FROM clientes_clinicas WHERE id = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        exit;
    }

    if ($action == 'eliminar') {
        $id = $_POST['id'];
        $stmt = $db->prepare("UPDATE clientes_clinicas SET estado = 0 WHERE id = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        echo json_encode(["status" => "success"]);
        exit;
    }

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    exit;
}
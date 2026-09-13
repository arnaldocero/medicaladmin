<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');
session_start();
require_once "../class/Conexion.php";

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
        $id = $_POST['idUserC'] ?? '';
        $pass = $_POST['password'];
        $params = [
            ":idC" => $_POST['clinica_id_hidden'],
            ":nom" => $_POST['nombre_uc'],
            ":usr" => $_POST['usuario_uc'],
            ":ema" => $_POST['email_uc'],
            ":rol_id" => $_POST['rol_id']
        ];

        if (!empty($id)) {
            $sql = "UPDATE usuarios_clinicas SET nombre=:nom, usuario=:usr, email=:ema, rol_id=:rol_id WHERE id=:id";
            if(!empty($pass)){
                $sql = "UPDATE usuarios_clinicas SET nombre=:nom, usuario=:usr, email=:ema, rol_id=:rol_id, password=:pass WHERE id=:id";
                $params[":pass"] = password_hash($pass, PASSWORD_DEFAULT);
            }
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":id", $id);
        } else {
            $sql = "INSERT INTO usuarios_clinicas (clinica_id, nombre, usuario, email, password, rol_id, estado) 
                    VALUES (:idC, :nom, :usr, :ema, :pass, :rol_id, 1)";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":pass", password_hash($pass, PASSWORD_DEFAULT));
        }

        foreach($params as $k => $v) $stmt->bindValue($k, $v);
        if($stmt->execute()) echo json_encode(["status" => "success"]);
        exit;
    }

    if ($action == 'obtener') {
        $stmt = $db->prepare("SELECT * FROM usuarios_clinicas WHERE id = :id");
        $stmt->execute([":id" => $_POST['id']]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        exit;
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
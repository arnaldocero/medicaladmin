<?php
session_start();
require_once "../class/Conexion.php";

if(isset($_POST["email"])) {
    $email = $_POST["email"];
    $pass = $_POST["password"];

    $stmt = Conexion::conectar()->prepare("SELECT * FROM usuarios_admin WHERE email = :email AND estado = 1");
    $stmt->bindParam(":email", $email, PDO::PARAM_STR);
    $stmt->execute();
    
    // LO HACEMOS UNA SOLA VEZ
    $usuario = $stmt->fetch();

    if(!$usuario){
        echo json_encode(["status" => "error", "message" => "El email no existe o está inactivo"]);
        exit;
    }

    if(password_verify($pass, $usuario["password"])) {
        // Llenamos la sesión
        $_SESSION["validar"] = "ok";
        $_SESSION["id"] = $usuario["id"];
        $_SESSION["nombre"] = $usuario["nombre"];
        $_SESSION["rol"] = $usuario["rol"];

        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "La contraseña es incorrecta"]);
    }
}
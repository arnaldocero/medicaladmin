<?php
class Conexion {
    public static function conectar() {
        $host = "localhost";
        $db   = "";
        $user = "";
        $pass = ""; // Tu contraseña de MySQL

        try {
            $link = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
            $link->exec("set names utf8");
            return $link;
        } catch (Exception $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
}
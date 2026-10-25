<?php
class Conexion {
    public static function conectar() {
        $archivo = __DIR__ . "/../config.php";
        if (!file_exists($archivo)) {
            die("Falta el archivo config.php (copia config.example.php y complétalo).");
        }
        $config = require $archivo;

        try {
            $link = new PDO(
                "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4",
                $config['db_user'],
                $config['db_pass'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            return $link;
        } catch (Exception $e) {
            // En el servidor no se muestran detalles (host, usuario...) al visitante
            error_log("Error de conexión: " . $e->getMessage());
            die(!empty($config['debug']) ? "Error de conexión: " . $e->getMessage() : "Error de conexión a la base de datos.");
        }
    }
}

<?php
// Mostramos errores de PHP por si acaso
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. Probamos si la ruta al archivo es correcta
if (file_exists("class/Conexion.php")) {
    require_once "class/Conexion.php";
    echo "✅ Archivo Conexion.php encontrado.<br>";
} else {
    die("❌ ERROR: No se encuentra el archivo en 'class/Conexion.php'. Revisa si la carpeta se llama 'class' o 'classes'.");
}

// 2. Probamos la conexión a la base de datos
try {
    $conexion = Conexion::conectar();
    if ($conexion) {
        echo "✅ Conexión exitosa a la base de datos 'administrador'.<br>";
        
        // 3. Probamos si la tabla existe
        $consulta = $conexion->query("SELECT COUNT(*) as total FROM usuarios_admin");
        $resultado = $consulta->fetch();
        echo "✅ Tabla 'usuarios_admin' encontrada. Total de usuarios: " . $resultado['total'];
    }
} catch (Exception $e) {
    echo "❌ ERROR DE CONEXIÓN: " . $e->getMessage();
}
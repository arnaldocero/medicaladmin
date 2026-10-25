<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once "../includes/sesion.php";
iniciarSesion();

// Límites contra fuerza bruta
const MAX_FALLOS_EMAIL = 5;   // por email en la ventana
const MAX_FALLOS_IP    = 20;  // por IP en la ventana (varios emails)
const VENTANA_MINUTOS  = 15;

// Mismo mensaje si el email no existe, está inactivo o la contraseña falla
const MENSAJE_FALLO = "Correo o contraseña incorrectos";

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST["email"], $_POST["password"])) {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método no permitido"]);
    exit;
}

$email = strtolower(trim($_POST["email"]));
$pass  = (string)$_POST["password"];
$ip    = $_SERVER['REMOTE_ADDR'] ?? '';

try {
    $db = Conexion::conectar();

    // Si la tabla de intentos aún no existe en esta BD, el login sigue funcionando sin límite
    $usarLimite = true;
    try {
        $stmt = $db->prepare("SELECT
                (SELECT COUNT(*) FROM login_intentos_admin WHERE email = :e AND exitoso = 0 AND fecha > NOW() - INTERVAL " . VENTANA_MINUTOS . " MINUTE) AS fallos_email,
                (SELECT COUNT(*) FROM login_intentos_admin WHERE ip = :ip AND exitoso = 0 AND fecha > NOW() - INTERVAL " . VENTANA_MINUTOS . " MINUTE) AS fallos_ip");
        $stmt->execute([":e" => $email, ":ip" => $ip]);
        $fallos = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("login_ajax: falta la tabla login_intentos_admin (" . $e->getMessage() . ")");
        $usarLimite = false;
    }

    if ($usarLimite && ($fallos["fallos_email"] >= MAX_FALLOS_EMAIL || $fallos["fallos_ip"] >= MAX_FALLOS_IP)) {
        http_response_code(429);
        echo json_encode(["status" => "error", "message" => "Demasiados intentos fallidos. Espera " . VENTANA_MINUTOS . " minutos e inténtalo de nuevo."]);
        exit;
    }

    $stmt = $db->prepare("SELECT id, nombre, rol, password FROM usuarios_admin WHERE email = :email AND estado = 1");
    $stmt->bindParam(":email", $email, PDO::PARAM_STR);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si el usuario no existe se verifica igual contra un hash ficticio,
    // para que el tiempo de respuesta no delate qué emails existen
    $hash = $usuario ? $usuario["password"] : '$2y$10$l3jkNxstyyfYtFrveUn3SOtvyZWlPrjAS9AHHImJFo19UtHKKkDdi';
    $correcto = password_verify($pass, $hash) && $usuario;

    if ($usarLimite) {
        $db->prepare("INSERT INTO login_intentos_admin (email, ip, exitoso) VALUES (:e, :ip, :ok)")
           ->execute([":e" => $email, ":ip" => $ip, ":ok" => $correcto ? 1 : 0]);
    }

    if (!$correcto) {
        echo json_encode(["status" => "error", "message" => MENSAJE_FALLO]);
        exit;
    }

    // Login correcto: los fallos previos de este email dejan de contar
    if ($usarLimite) {
        $db->prepare("DELETE FROM login_intentos_admin WHERE email = :e AND exitoso = 0")->execute([":e" => $email]);
    }

    // Nuevo ID de sesión para evitar fijación de sesión, y token CSRF nuevo
    session_regenerate_id(true);
    $_SESSION["validar"] = "ok";
    $_SESSION["id"] = $usuario["id"];
    $_SESSION["nombre"] = $usuario["nombre"];
    $_SESSION["rol"] = $usuario["rol"];
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    $_SESSION["ultima_actividad"] = time();

    echo json_encode(["status" => "success"]);
} catch (Exception $e) {
    error_log("login_ajax: " . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Error al iniciar sesión. Inténtalo más tarde."]);
}

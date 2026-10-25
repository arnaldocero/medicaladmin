<?php
// Protección CSRF para los endpoints AJAX.
// El token se crea en includes/header.php y utils.js lo envía en la cabecera X-CSRF-Token.
function verificarCsrf() {
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if ($_SERVER['REQUEST_METHOD'] !== 'POST'
        || empty($_SESSION['csrf_token'])
        || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        echo json_encode(["status" => "error", "message" => "Solicitud no válida. Recarga la página e inténtalo de nuevo."]);
        exit;
    }
}

<?php
// Utilidades de validación para los endpoints AJAX.

// Responde un error de validación en JSON y termina
function errorValidacion($mensaje) {
    http_response_code(422);
    echo json_encode(["status" => "error", "message" => $mensaje]);
    exit;
}

// Texto del POST recortado; valida obligatorio y longitud máxima
function textoPost($campo, $etiqueta, $max, $obligatorio = true) {
    $valor = trim((string)($_POST[$campo] ?? ''));
    if ($obligatorio && $valor === '') errorValidacion("$etiqueta es obligatorio");
    if (mb_strlen($valor) > $max) errorValidacion("$etiqueta admite máximo $max caracteres");
    return $valor;
}

// Email del POST; vacío solo se permite si no es obligatorio
function emailPost($campo, $etiqueta, $obligatorio = true) {
    $valor = textoPost($campo, $etiqueta, 100, $obligatorio);
    if ($valor !== '' && !filter_var($valor, FILTER_VALIDATE_EMAIL)) errorValidacion("$etiqueta no es un correo válido");
    return strtolower($valor);
}

// Fecha Y-m-d válida del POST
function fechaPost($campo, $etiqueta) {
    $valor = trim((string)($_POST[$campo] ?? ''));
    $fecha = DateTime::createFromFormat('!Y-m-d', $valor);
    if (!$fecha || $fecha->format('Y-m-d') !== $valor) errorValidacion("$etiqueta no es una fecha válida");
    return $valor;
}

// Contraseña: obligatoria al crear, opcional al editar (vacía = no cambiar)
function passwordPost($campo, $obligatorio) {
    $valor = (string)($_POST[$campo] ?? '');
    if ($valor === '') {
        if ($obligatorio) errorValidacion("La contraseña es obligatoria para un usuario nuevo");
        return '';
    }
    if (strlen($valor) < 8) errorValidacion("La contraseña debe tener al menos 8 caracteres");
    return $valor;
}

// ID entero positivo del POST, o null si viene vacío
function idPost($campo) {
    $valor = $_POST[$campo] ?? '';
    if ($valor === '' || $valor === null) return null;
    if (!ctype_digit((string)$valor) || (int)$valor <= 0) errorValidacion("Identificador no válido");
    return (int)$valor;
}

// true si la excepción es por clave única duplicada
function esDuplicado(Exception $e) {
    return $e instanceof PDOException && ($e->errorInfo[1] ?? 0) == 1062;
}

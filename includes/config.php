<?php
// Lectura de config.php con valores por defecto para las claves opcionales.

function config($clave, $defecto = null) {
    static $config = null;
    if ($config === null) {
        $archivo = __DIR__ . "/../config.php";
        $config = file_exists($archivo) ? (require $archivo) : [];
    }
    return array_key_exists($clave, $config) ? $config[$clave] : $defecto;
}

// URL pública del panel sin "/" final (para los enlaces de los correos).
// Se toma de config.php, nunca de la cabecera Host de la petición, que la puede falsear quien la envía.
function urlBase() {
    return rtrim((string)config('url_base', ''), '/');
}

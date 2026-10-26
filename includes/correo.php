<?php
// Envío de correos del panel.
// config.php → 'correo_modo':
//   'mail'    (por defecto) usa mail() de PHP, que en cPanel funciona sin más configuración.
//   'archivo' guarda cada correo como HTML en logs/correos/ (para probar en local, XAMPP no envía correos).
require_once __DIR__ . "/config.php";

// Envía un correo HTML. Devuelve true si se entregó al servidor de correo (o se guardó en archivo).
function enviarCorreo($para, $asunto, $contenidoHtml) {
    if (!filter_var($para, FILTER_VALIDATE_EMAIL)) return false;

    $remitente = config('correo_remitente', '');
    $nombreRemitente = config('correo_nombre', 'Sistema Clínico');
    $html = plantillaCorreo($asunto, $contenidoHtml);

    if (config('correo_modo', 'mail') === 'archivo') {
        $carpeta = __DIR__ . "/../logs/correos";
        if (!is_dir($carpeta)) mkdir($carpeta, 0750, true);
        $nombre = date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.html';
        $cabecera = "<!-- Para: " . htmlspecialchars($para) . " | Asunto: " . htmlspecialchars($asunto) . " -->\n";
        return file_put_contents("$carpeta/$nombre", $cabecera . $html) !== false;
    }

    if (!filter_var($remitente, FILTER_VALIDATE_EMAIL)) {
        error_log("correo: falta 'correo_remitente' en config.php; no se envió '$asunto' a $para");
        return false;
    }

    // Asunto y nombre codificados para que las tildes lleguen bien
    $asuntoCodificado = '=?UTF-8?B?' . base64_encode($asunto) . '?=';
    $deCodificado = '=?UTF-8?B?' . base64_encode($nombreRemitente) . '?=';
    $cabeceras = implode("\r\n", [
        "MIME-Version: 1.0",
        "Content-Type: text/html; charset=UTF-8",
        "From: $deCodificado <$remitente>",
        "Reply-To: $remitente",
    ]);
    $ok = mail($para, $asuntoCodificado, $html, $cabeceras, "-f$remitente");
    if (!$ok) error_log("correo: mail() falló al enviar '$asunto' a $para");
    return $ok;
}

// Envoltorio HTML sencillo y legible en cualquier cliente de correo
function plantillaCorreo($titulo, $contenidoHtml) {
    $t = htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8');
    return <<<HTML
<!doctype html>
<html lang="es"><head><meta charset="utf-8"><title>$t</title></head>
<body style="margin:0;padding:24px;background:#f1f2f7;font-family:Arial,Helvetica,sans-serif;color:#333;">
  <div style="max-width:560px;margin:0 auto;background:#fff;border-radius:6px;padding:24px;">
    <h2 style="margin-top:0;color:#272c33;font-size:20px;">$t</h2>
    $contenidoHtml
    <p style="margin-top:32px;font-size:12px;color:#999;">Sistema Clínico · Panel administrativo. Este es un mensaje automático.</p>
  </div>
</body></html>
HTML;
}

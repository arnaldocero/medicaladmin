<?php
// Avisos por email de membresías por vencer. Se ejecuta una vez al día desde el Cron de cPanel:
//   php /home/USUARIO/public_html/HMSadmin/cron/avisos_vencimiento.php
//
// - Umbrales en config.php → 'avisos_dias' (por defecto 30, 15, 7, 1 y 0 días antes).
// - Cada aviso se envía una sola vez por clínica, fecha de vencimiento y umbral (tabla avisos_vencimiento).
//   Si el cron no corrió algún día, en la siguiente ejecución se envía el umbral que corresponda.
// - Siempre: un resumen a los superadmins activos. Si 'avisos_a_clinicas' es true, también a cada clínica.

if (php_sapi_name() !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . "/../class/Conexion.php";
require_once __DIR__ . "/../includes/config.php";
require_once __DIR__ . "/../includes/correo.php";
require_once __DIR__ . "/../includes/planes.php";

$umbrales = array_map('intval', (array)config('avisos_dias', [30, 15, 7, 1, 0]));
$umbrales = array_values(array_unique(array_filter($umbrales, function($d) { return $d >= 0; })));
sort($umbrales);
if (!$umbrales) exit("Sin umbrales configurados.\n");
$avisarClinicas = (bool)config('avisos_a_clinicas', false);

$db = Conexion::conectar();

// Umbral que le toca a una clínica según los días que le quedan: el menor umbral >= días restantes
function umbralPara($diasRestantes, array $umbrales) {
    foreach ($umbrales as $u) if ($diasRestantes <= $u) return $u;
    return null;
}

function yaAvisado(PDO $db, $clinica, $fecha, $umbral, $destino) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM avisos_vencimiento
        WHERE clinica_id = :c AND fecha_vencimiento = :f AND dias_antes = :d AND destino = :dest");
    $stmt->execute([":c" => $clinica, ":f" => $fecha, ":d" => $umbral, ":dest" => $destino]);
    return (bool)$stmt->fetchColumn();
}

function marcarAvisado(PDO $db, $clinica, $fecha, $umbral, $destino, $email) {
    $db->prepare("INSERT IGNORE INTO avisos_vencimiento (clinica_id, fecha_vencimiento, dias_antes, destino, email)
                  VALUES (:c, :f, :d, :dest, :e)")
       ->execute([":c" => $clinica, ":f" => $fecha, ":d" => $umbral, ":dest" => $destino, ":e" => $email]);
}

function textoDias($dias) {
    return $dias == 0 ? "vence HOY" : ($dias == 1 ? "vence mañana" : "vence en $dias días");
}

$h = function($t) { return htmlspecialchars((string)$t, ENT_QUOTES, 'UTF-8'); };

// Clínicas activas cuya membresía vence entre hoy y el umbral mayor
$stmt = $db->prepare("SELECT c.id, c.nombre_clinica, c.nit_empresa, c.email_contacto, c.representante_legal,
        c.fecha_vencimiento, DATEDIFF(c.fecha_vencimiento, CURDATE()) AS dias,
        COALESCE(p.nombre, c.plan_contratado) AS plan, p.precio_mensual
    FROM clientes_clinicas c
    LEFT JOIN planes p ON p.codigo = c.plan_contratado
    WHERE c.estado = 1 AND c.fecha_vencimiento BETWEEN CURDATE() AND CURDATE() + INTERVAL :max DAY
    ORDER BY c.fecha_vencimiento");
$stmt->execute([":max" => max($umbrales)]);
$clinicas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$paraResumen = [];
$enviadosClinicas = 0;

foreach ($clinicas as $c) {
    $umbral = umbralPara((int)$c["dias"], $umbrales);
    if ($umbral === null) continue;

    // Aviso a la clínica (solo si está activado en config.php y tiene email válido)
    if ($avisarClinicas && filter_var($c["email_contacto"], FILTER_VALIDATE_EMAIL)
        && !yaAvisado($db, $c["id"], $c["fecha_vencimiento"], $umbral, "clinica")) {
        $ok = enviarCorreo($c["email_contacto"], "Su membresía " . textoDias($c["dias"]), "
            <p>Hola" . ($c["representante_legal"] ? " " . $h($c["representante_legal"]) : "") . ",</p>
            <p>Le recordamos que la membresía de <b>" . $h($c["nombre_clinica"]) . "</b> (plan " . $h($c["plan"]) . ")
               <b>" . textoDias($c["dias"]) . "</b>, el <b>" . $h($c["fecha_vencimiento"]) . "</b>.</p>
            <p>Para evitar interrupciones en el servicio, contáctenos para renovarla.</p>");
        if ($ok) {
            marcarAvisado($db, $c["id"], $c["fecha_vencimiento"], $umbral, "clinica", $c["email_contacto"]);
            $enviadosClinicas++;
        }
    }

    if (!yaAvisado($db, $c["id"], $c["fecha_vencimiento"], $umbral, "superadmins")) {
        $paraResumen[] = $c + ["umbral" => $umbral];
    }
}

// Resumen para los superadmins activos
$enviadosResumen = 0;
if ($paraResumen) {
    $filas = '';
    foreach ($paraResumen as $c) {
        $color = $c["dias"] <= 1 ? '#dc3545' : ($c["dias"] <= 7 ? '#e67e22' : '#333');
        $filas .= "<tr>
            <td style=\"padding:6px;border-bottom:1px solid #eee;\"><b>" . $h($c["nombre_clinica"]) . "</b><br><small style=\"color:#888;\">NIT " . $h($c["nit_empresa"]) . "</small></td>
            <td style=\"padding:6px;border-bottom:1px solid #eee;\">" . $h($c["plan"]) . "</td>
            <td style=\"padding:6px;border-bottom:1px solid #eee;\">" . $h($c["fecha_vencimiento"]) . "</td>
            <td style=\"padding:6px;border-bottom:1px solid #eee;color:$color;\"><b>" . textoDias($c["dias"]) . "</b></td>
        </tr>";
    }
    $enlace = urlBase() !== '' ? "<p><a href=\"" . $h(urlBase() . "/membresias.php") . "\">Abrir Membresías en el panel</a></p>" : '';
    $contenido = "<p>Estas membresías están por vencer:</p>
        <table style=\"width:100%;border-collapse:collapse;font-size:14px;\">
          <tr style=\"background:#f1f2f7;text-align:left;\"><th style=\"padding:6px;\">Clínica</th><th style=\"padding:6px;\">Plan</th><th style=\"padding:6px;\">Vence</th><th style=\"padding:6px;\"></th></tr>
          $filas
        </table>
        <p style=\"font-size:13px;color:#666;\">" . ($avisarClinicas ? "A las clínicas con email de contacto también se les envió un recordatorio." : "Las clínicas no reciben aviso automático (avisos_a_clinicas = false).") . "</p>
        $enlace";

    $superadmins = $db->query("SELECT email FROM usuarios_admin WHERE rol = 'superadmin' AND estado = 1")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($superadmins as $email) {
        if (enviarCorreo($email, count($paraResumen) . " membresía(s) por vencer", $contenido)) $enviadosResumen++;
    }
    // Se marca como avisado si al menos un superadmin lo recibió
    if ($enviadosResumen > 0) {
        foreach ($paraResumen as $c) {
            marcarAvisado($db, $c["id"], $c["fecha_vencimiento"], $c["umbral"], "superadmins", null);
        }
    }
}

echo date('Y-m-d H:i') . " avisos_vencimiento: " . count($clinicas) . " clínica(s) en ventana, "
   . count($paraResumen) . " en el resumen ($enviadosResumen superadmin(s)), $enviadosClinicas aviso(s) a clínicas.\n";

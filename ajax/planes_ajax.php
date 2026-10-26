<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once "../includes/sesion.php";
require_once "../includes/csrf.php";
require_once "../includes/validacion.php";
require_once "../includes/bitacora.php";

verificarSesion(["superadmin"], true);

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Todo lo que no sea listar solo por POST con token CSRF
if ($action !== 'listar') {
    verificarCsrf();
}

// Datos de un plan para la bitácora
function fotoPlan(PDO $db, $id) {
    $stmt = $db->prepare("SELECT codigo, nombre, precio_mensual, max_usuarios FROM planes WHERE id = :id");
    $stmt->execute([":id" => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

try {
    $db = Conexion::conectar();

    if ($action == 'listar') {
        // Uso de cada plan: clínicas activas, vigentes y la que más usuarios tiene
        $stmt = $db->query("SELECT p.id, p.codigo, p.nombre, p.precio_mensual, p.max_usuarios,
                COUNT(c.id) AS clinicas,
                COALESCE(SUM(c.fecha_vencimiento >= CURDATE()), 0) AS vigentes,
                COALESCE(MAX(u.activos), 0) AS max_en_uso
            FROM planes p
            LEFT JOIN clientes_clinicas c ON c.plan_contratado = p.codigo AND c.estado = 1
            LEFT JOIN (SELECT clinica_id, COUNT(*) AS activos FROM usuarios_clinicas WHERE estado = 1 GROUP BY clinica_id) u
                   ON u.clinica_id = c.id
            GROUP BY p.id ORDER BY p.orden, p.id");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    if ($action == 'obtener') {
        $stmt = $db->prepare("SELECT id, codigo, nombre, precio_mensual, max_usuarios FROM planes WHERE id = :id");
        $stmt->bindValue(":id", idPost('id'), PDO::PARAM_INT);
        $stmt->execute();
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        exit;
    }

    if ($action == 'guardar') {
        // Solo se editan los planes existentes: el código está ligado a clientes_clinicas.plan_contratado
        $id = idPost('idPlan');
        if ($id === null) errorValidacion("Identificador no válido");
        $antes = fotoPlan($db, $id);
        if (!$antes) errorValidacion("El plan no existe");

        $nombre = textoPost('nombre', 'El nombre', 50);

        // Viene de un <input type="number">: punto como separador decimal, sin separador de miles
        $precio = trim((string)($_POST['precio_mensual'] ?? ''));
        if ($precio === '' || !is_numeric($precio) || (float)$precio < 0 || (float)$precio > 9999999999) {
            errorValidacion("El precio mensual debe ser un número mayor o igual a 0");
        }

        $max = trim((string)($_POST['max_usuarios'] ?? ''));
        if ($max !== '' && (!ctype_digit($max) || (int)$max < 1 || (int)$max > 100000)) {
            errorValidacion("El límite de usuarios debe ser un número entero de 1 en adelante, o vacío para ilimitado");
        }

        $stmt = $db->prepare("UPDATE planes SET nombre = :n, precio_mensual = :p, max_usuarios = :m WHERE id = :id");
        $stmt->execute([":n" => $nombre, ":p" => round((float)$precio, 2), ":m" => $max === '' ? null : (int)$max, ":id" => $id]);

        $cambios = cambiosEntre($antes, fotoPlan($db, $id));
        if ($cambios) {
            registrarBitacora($db, "editar", "plan", $id, "Editó el plan " . $antes["codigo"], $cambios);
        }

        // Aviso si con el nuevo límite ya hay clínicas excedidas (no se desactiva a nadie)
        $aviso = null;
        if ($max !== '') {
            $stmt = $db->prepare("SELECT COUNT(*) FROM clientes_clinicas c
                WHERE c.estado = 1 AND c.plan_contratado = :codigo
                  AND (SELECT COUNT(*) FROM usuarios_clinicas u WHERE u.clinica_id = c.id AND u.estado = 1) > :max");
            $stmt->execute([":codigo" => $antes["codigo"], ":max" => (int)$max]);
            $excedidas = (int)$stmt->fetchColumn();
            if ($excedidas) $aviso = "$excedidas clínica(s) de este plan ya superan el nuevo límite. Aparecen en el dashboard.";
        }

        echo json_encode(["status" => "success", "aviso" => $aviso]);
        exit;
    }

    echo json_encode(["status" => "error", "message" => "Acción no válida"]);
} catch (Exception $e) {
    error_log("planes_ajax: " . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Error al procesar la solicitud"]);
}

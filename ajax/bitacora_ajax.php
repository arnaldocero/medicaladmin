<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once "../includes/sesion.php";

verificarSesion(["superadmin"], true);

// Máximo de registros por consulta; para ver más atrás se acota con las fechas
const LIMITE_BITACORA = 5000;

$action = $_GET['action'] ?? '';

try {
    $db = Conexion::conectar();

    if ($action == 'listar') {
        $where = [];
        $params = [];

        // Fechas Y-m-d (inclusive); valores inválidos se ignoran
        foreach (["desde" => ">=", "hasta" => "<"] as $campo => $op) {
            $valor = $_GET[$campo] ?? '';
            $fecha = DateTime::createFromFormat('!Y-m-d', $valor);
            if ($fecha && $fecha->format('Y-m-d') === $valor) {
                if ($campo === "hasta") $fecha->modify('+1 day');
                $where[] = "b.fecha $op :$campo";
                $params[":$campo"] = $fecha->format('Y-m-d');
            }
        }
        if (in_array($_GET['entidad'] ?? '', ['clinica', 'usuario_clinica', 'usuario_admin', 'sesion'], true)) {
            $where[] = "b.entidad = :entidad";
            $params[":entidad"] = $_GET['entidad'];
        }
        if (in_array($_GET['accion_filtro'] ?? '', ['crear', 'editar', 'eliminar', 'desactivar', 'activar', 'login'], true)) {
            $where[] = "b.accion = :accion";
            $params[":accion"] = $_GET['accion_filtro'];
        }
        if (ctype_digit((string)($_GET['usuario_id'] ?? ''))) {
            $where[] = "b.usuario_id = :uid";
            $params[":uid"] = (int)$_GET['usuario_id'];
        }

        $sql = "SELECT b.id, b.fecha, b.usuario_nombre, b.accion, b.entidad, b.entidad_id, b.descripcion, b.cambios, b.ip
                FROM bitacora_admin b"
             . ($where ? " WHERE " . implode(" AND ", $where) : "")
             . " ORDER BY b.fecha DESC, b.id DESC LIMIT " . LIMITE_BITACORA;
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($filas as &$f) {
            $f["cambios"] = $f["cambios"] ? json_decode($f["cambios"], true) : null;
        }
        echo json_encode(["filas" => $filas, "limite" => LIMITE_BITACORA]);
        exit;
    }

    echo json_encode(["status" => "error", "message" => "Acción no válida"]);
} catch (Exception $e) {
    error_log("bitacora_ajax: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "No se pudo leer la bitácora. ¿Se creó la tabla bitacora_admin?"]);
}

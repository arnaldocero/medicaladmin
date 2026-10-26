<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once "../includes/sesion.php";
require_once "../includes/csrf.php";
require_once "../includes/validacion.php";
require_once "../includes/bitacora.php";
require_once "../includes/planes.php";

verificarSesion(["superadmin"], true);

// Datos de una clínica para la bitácora
function fotoClinica(PDO $db, $id) {
    $stmt = $db->prepare("SELECT nit_empresa, nombre_clinica, representante_legal, telefono, email_contacto,
            plan_contratado, fecha_activacion, fecha_vencimiento
        FROM clientes_clinicas WHERE id = :id");
    $stmt->execute([":id" => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Todo lo que no sea listar modifica o lee datos concretos: solo por POST con token CSRF
if ($action !== 'listar') {
    verificarCsrf();
}

try {
    $db = Conexion::conectar();

    if ($action == 'listar') {
        // Con el nombre del plan, su límite y los usuarios activos de cada clínica
        $stmt = $db->prepare("SELECT c.*, p.nombre AS plan_nombre, p.max_usuarios,
                (SELECT COUNT(*) FROM usuarios_clinicas u WHERE u.clinica_id = c.id AND u.estado = 1) AS usuarios_activos
            FROM clientes_clinicas c
            LEFT JOIN planes p ON p.codigo = c.plan_contratado
            WHERE c.estado = 1 ORDER BY c.id DESC");
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    if ($action == 'guardar') {
        $id = idPost('idCliente');

        $nit = textoPost('nit_empresa', 'El NIT', 20);
        if (!preg_match('/^[0-9A-Za-z.\- ]+$/', $nit)) errorValidacion("El NIT solo admite números, letras, puntos y guiones");
        $telefono = textoPost('telefono', 'El teléfono', 20, false);
        if ($telefono !== '' && !preg_match('/^[0-9+\-() ]+$/', $telefono)) errorValidacion("El teléfono solo admite números, espacios y + - ( )");
        $plan = $_POST['plan_contratado'] ?? '';
        $planes = obtenerPlanes($db);
        if (!isset($planes[$plan])) errorValidacion("Plan no válido");

        $activacion = fechaPost('fecha_activacion', 'La fecha de activación');
        $vencimiento = fechaPost('fecha_vencimiento', 'La fecha de vencimiento');
        if ($vencimiento <= $activacion) errorValidacion("La fecha de vencimiento debe ser posterior a la de activación");

        $datos = [
            ":nit" => $nit,
            ":nom" => textoPost('nombre_clinica', 'El nombre de la clínica', 200),
            ":rep" => textoPost('representante_legal', 'El representante legal', 150, false),
            ":tel" => $telefono,
            ":ema" => emailPost('email_contacto', 'El email de contacto', false),
            ":pla" => $plan,
            ":f_a" => $activacion,
            ":f_v" => $vencimiento
        ];

        $antes = $id !== null ? fotoClinica($db, $id) : null;

        if ($id !== null) {
            // EDITAR
            $sql = "UPDATE clientes_clinicas SET nit_empresa=:nit, nombre_clinica=:nom, representante_legal=:rep,
                    telefono=:tel, email_contacto=:ema, plan_contratado=:pla, fecha_activacion=:f_a,
                    fecha_vencimiento=:f_v WHERE id=:id";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        } else {
            // NUEVO
            $sql = "INSERT INTO clientes_clinicas (nit_empresa, nombre_clinica, representante_legal, telefono,
                    email_contacto, plan_contratado, fecha_activacion, fecha_vencimiento, estado)
                    VALUES (:nit, :nom, :rep, :tel, :ema, :pla, :f_a, :f_v, 1)";
            $stmt = $db->prepare($sql);
        }

        foreach ($datos as $key => $val) {
            $stmt->bindValue($key, $val);
        }

        try {
            $stmt->execute();
        } catch (PDOException $e) {
            if (esDuplicado($e)) errorValidacion("Ya existe una clínica con ese NIT (puede estar inactiva)");
            throw $e;
        }

        $idGuardado = $id ?? (int)$db->lastInsertId();
        $cambios = cambiosEntre($antes, fotoClinica($db, $idGuardado));
        $etiqueta = $datos[":nom"] . " (NIT " . $nit . ")";
        if ($id === null) {
            registrarBitacora($db, "crear", "clinica", $idGuardado, "Creó la clínica $etiqueta", $cambios);
        } elseif ($cambios) {
            registrarBitacora($db, "editar", "clinica", $idGuardado, "Editó la clínica $etiqueta", $cambios);
        }

        // Si el nuevo plan tiene un límite menor que los usuarios activos actuales, se avisa (no se desactiva a nadie)
        $aviso = null;
        $max = $planes[$plan]["max_usuarios"];
        if ($id !== null && $max !== null) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM usuarios_clinicas WHERE clinica_id = :id AND estado = 1");
            $stmt->execute([":id" => $idGuardado]);
            $activos = (int)$stmt->fetchColumn();
            if ($activos > (int)$max) {
                $aviso = "La clínica tiene $activos usuarios activos y el plan " . $planes[$plan]["nombre"]
                       . " permite " . (int)$max . ". No podrá crear ni reactivar usuarios hasta bajar de ese número.";
            }
        }

        echo json_encode(["status" => "success", "aviso" => $aviso]);
        exit;
    }

    if ($action == 'obtener') {
        $stmt = $db->prepare("SELECT * FROM clientes_clinicas WHERE id = :id");
        $stmt->bindValue(":id", idPost('id'), PDO::PARAM_INT);
        $stmt->execute();
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        exit;
    }

    if ($action == 'eliminar') {
        $id = idPost('id');
        if ($id === null) errorValidacion("Identificador no válido");
        $antes = fotoClinica($db, $id);
        $stmt = $db->prepare("UPDATE clientes_clinicas SET estado = 0 WHERE id = :id AND estado = 1");
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            registrarBitacora($db, "eliminar", "clinica", $id,
                "Eliminó la clínica " . ($antes["nombre_clinica"] ?? "#$id") . " (NIT " . ($antes["nit_empresa"] ?? "-") . ")",
                cambiosEntre($antes, null));
        }
        echo json_encode(["status" => "success"]);
        exit;
    }

    echo json_encode(["status" => "error", "message" => "Acción no válida"]);
} catch (Exception $e) {
    error_log("membresias_ajax: " . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Error al procesar la solicitud"]);
}

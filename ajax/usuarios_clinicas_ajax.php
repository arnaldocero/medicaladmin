<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once "../includes/sesion.php";
require_once "../includes/csrf.php";
require_once "../includes/validacion.php";
require_once "../includes/bitacora.php";
require_once "../includes/planes.php";

// Datos legibles de un usuario de clínica para la bitácora (clínica y rol por nombre)
function fotoUsuarioClinica(PDO $db, $id) {
    $stmt = $db->prepare("SELECT u.nombre, u.usuario, u.email, c.nombre_clinica AS clinica, r.nombre_rol AS rol
        FROM usuarios_clinicas u
        LEFT JOIN clientes_clinicas c ON u.clinica_id = c.id
        LEFT JOIN roles_clinicas r ON u.rol_id = r.id
        WHERE u.id = :id");
    $stmt->execute([":id" => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

// Solo personal del panel con sesión iniciada (admin o superadmin)
verificarSesion(ROLES_PANEL, true);

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Todo lo que no sea listar modifica o lee datos concretos: solo por POST con token CSRF
if ($action !== 'listar_global') {
    verificarCsrf();
}
try {
    $db = Conexion::conectar();

    if ($action == 'listar_global') {
        // Filtros opcionales: clinica_id y estado (1 = activos [por defecto], 0 = inactivos, todos)
        $where = [];
        $params = [];
        $estado = $_GET['estado'] ?? '1';
        if ($estado === '1' || $estado === '0') {
            $where[] = "u.estado = :estado";
            $params[":estado"] = (int)$estado;
        }
        if (ctype_digit((string)($_GET['clinica_id'] ?? ''))) {
            $where[] = "u.clinica_id = :clinica";
            $params[":clinica"] = (int)$_GET['clinica_id'];
        }

        $sql = "SELECT u.id, u.nombre, u.usuario, u.email, u.estado, r.nombre_rol as rol,
                       c.nombre_clinica, c.estado AS clinica_estado
                FROM usuarios_clinicas u
                INNER JOIN roles_clinicas r ON u.rol_id = r.id
                INNER JOIN clientes_clinicas c ON u.clinica_id = c.id"
             . ($where ? " WHERE " . implode(" AND ", $where) : "")
             . " ORDER BY c.nombre_clinica ASC, u.nombre ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    // Desactivar / reactivar. No se borra el registro: el usuario tiene citas,
    // historias clínicas, fórmulas, etc. asociadas que deben conservarse.
    if ($action == 'cambiar_estado') {
        $id = idPost('id');
        if ($id === null) errorValidacion("Identificador no válido");
        $nuevo = $_POST['estado'] ?? '';
        if ($nuevo !== '0' && $nuevo !== '1') errorValidacion("Estado no válido");
        $nuevo = (int)$nuevo;

        $stmt = $db->prepare("SELECT u.usuario, u.estado, u.clinica_id, c.estado AS clinica_estado
            FROM usuarios_clinicas u INNER JOIN clientes_clinicas c ON u.clinica_id = c.id WHERE u.id = :id");
        $stmt->execute([":id" => $id]);
        $actual = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$actual) errorValidacion("El usuario no existe");

        if ($nuevo === 1) {
            if (!(int)$actual["clinica_estado"]) errorValidacion("No se puede reactivar: la clínica del usuario está inactiva");
            // Mientras estuvo inactivo, otro usuario activo pudo tomar el mismo nombre de usuario
            $stmt = $db->prepare("SELECT COUNT(*) FROM usuarios_clinicas WHERE usuario = :usr AND estado = 1 AND id <> :id");
            $stmt->execute([":usr" => $actual["usuario"], ":id" => $id]);
            if ($stmt->fetchColumn()) errorValidacion("No se puede reactivar: ya hay otro usuario activo llamado " . $actual["usuario"]);
            if ((int)$actual["estado"] !== 1) verificarLimiteUsuarios($db, $actual["clinica_id"], $id);
        }

        $stmt = $db->prepare("UPDATE usuarios_clinicas SET estado = :nuevo WHERE id = :id AND estado <> :nuevo2");
        $stmt->execute([":nuevo" => $nuevo, ":id" => $id, ":nuevo2" => $nuevo]);
        if ($stmt->rowCount() > 0) {
            $foto = fotoUsuarioClinica($db, $id);
            $etiqueta = $foto["usuario"] . " (" . $foto["clinica"] . ")";
            registrarBitacora($db, $nuevo ? "activar" : "desactivar", "usuario_clinica", $id,
                ($nuevo ? "Reactivó" : "Desactivó") . " el usuario de clínica $etiqueta",
                ["estado" => ["antes" => $nuevo ? "Inactivo" : "Activo", "despues" => $nuevo ? "Activo" : "Inactivo"]]);
        }
        echo json_encode(["status" => "success"]);
        exit;
    }

    if ($action == 'guardar') {
        $id = idPost('idUserC');
        $clinicaId = idPost('clinica_id_hidden');
        $rolId = idPost('rol_id');
        if ($clinicaId === null) errorValidacion("La clínica es obligatoria");
        if ($rolId === null) errorValidacion("El rol es obligatorio");

        $usuario = textoPost('usuario_uc', 'El usuario', 50);
        if (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $usuario)) {
            errorValidacion("El usuario debe tener al menos 3 caracteres: letras, números, punto, guion o guion bajo");
        }
        $pass = passwordPost('password', $id === null);

        $params = [
            ":idC" => $clinicaId,
            ":nom" => textoPost('nombre_uc', 'El nombre', 150),
            ":usr" => $usuario,
            ":ema" => emailPost('email_uc', 'El email', false),
            ":rol_id" => $rolId
        ];

        // La clínica debe existir y estar activa, y el rol debe existir
        $stmt = $db->prepare("SELECT COUNT(*) FROM clientes_clinicas WHERE id = :id AND estado = 1");
        $stmt->execute([":id" => $clinicaId]);
        if (!$stmt->fetchColumn()) errorValidacion("La clínica seleccionada no existe o está inactiva");

        $stmt = $db->prepare("SELECT COUNT(*) FROM roles_clinicas WHERE id = :id");
        $stmt->execute([":id" => $rolId]);
        if (!$stmt->fetchColumn()) errorValidacion("El rol seleccionado no existe");

        // El nombre de usuario no puede repetirse entre usuarios activos
        $stmt = $db->prepare("SELECT COUNT(*) FROM usuarios_clinicas WHERE usuario = :usr AND estado = 1 AND id <> :id");
        $stmt->execute([":usr" => $usuario, ":id" => $id ?? 0]);
        if ($stmt->fetchColumn()) errorValidacion("Ya existe un usuario activo con ese nombre de usuario");

        // Límite de usuarios del plan: al crear, o al mover un usuario activo a otra clínica
        if ($id === null) {
            verificarLimiteUsuarios($db, $clinicaId);
        } else {
            $stmt = $db->prepare("SELECT clinica_id, estado FROM usuarios_clinicas WHERE id = :id");
            $stmt->execute([":id" => $id]);
            $actualUC = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($actualUC && (int)$actualUC["estado"] === 1 && (int)$actualUC["clinica_id"] !== $clinicaId) {
                verificarLimiteUsuarios($db, $clinicaId, $id);
            }
        }

        $antes = $id !== null ? fotoUsuarioClinica($db, $id) : null;

        if ($id !== null) {
            $sql = "UPDATE usuarios_clinicas SET clinica_id=:idC, nombre=:nom, usuario=:usr, email=:ema, rol_id=:rol_id WHERE id=:id";
            if(!empty($pass)){
                $sql = "UPDATE usuarios_clinicas SET clinica_id=:idC, nombre=:nom, usuario=:usr, email=:ema, rol_id=:rol_id, password=:pass WHERE id=:id";
                $params[":pass"] = password_hash($pass, PASSWORD_DEFAULT);
            }
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        } else {
            $sql = "INSERT INTO usuarios_clinicas (clinica_id, nombre, usuario, email, password, rol_id, estado)
                    VALUES (:idC, :nom, :usr, :ema, :pass, :rol_id, 1)";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":pass", password_hash($pass, PASSWORD_DEFAULT));
        }

        foreach($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();

        $idGuardado = $id ?? (int)$db->lastInsertId();
        $despues = fotoUsuarioClinica($db, $idGuardado);
        $cambios = cambiosEntre($antes, $despues);
        if ($pass !== '' && $id !== null) $cambios["password"] = ["antes" => null, "despues" => "(contraseña cambiada)"];
        $etiqueta = $despues["usuario"] . " (" . $despues["clinica"] . ")";
        if ($id === null) {
            registrarBitacora($db, "crear", "usuario_clinica", $idGuardado, "Creó el usuario de clínica $etiqueta", $cambios);
        } elseif ($cambios) {
            registrarBitacora($db, "editar", "usuario_clinica", $idGuardado, "Editó el usuario de clínica $etiqueta", $cambios);
        }

        echo json_encode(["status" => "success"]);
        exit;
    }

    if ($action == 'obtener') {
        // Sin la columna password: el hash nunca debe llegar al navegador
        $stmt = $db->prepare("SELECT id, clinica_id, nombre, usuario, email, rol_id FROM usuarios_clinicas WHERE id = :id");
        $stmt->bindValue(":id", $_POST['id'] ?? 0, PDO::PARAM_INT);
        $stmt->execute();
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        exit;
    }

    echo json_encode(["status" => "error", "message" => "Acción no válida"]);
} catch (Exception $e) {
    error_log("usuarios_clinicas_ajax: " . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Error al procesar la solicitud"]);
}

<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once "../includes/sesion.php";
require_once "../includes/csrf.php";
require_once "../includes/validacion.php";

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
        $sql = "SELECT u.id, u.nombre, u.usuario, u.email, r.nombre_rol as rol, c.nombre_clinica
                FROM usuarios_clinicas u
                INNER JOIN roles_clinicas r ON u.rol_id = r.id
                INNER JOIN clientes_clinicas c ON u.clinica_id = c.id
                WHERE u.estado = 1 ORDER BY c.nombre_clinica ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
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
        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error al guardar"]);
        }
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

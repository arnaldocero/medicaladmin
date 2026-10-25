<?php
// Registro de acciones en la tabla bitacora_admin.

// Guarda una acción. Si la tabla no existe o falla, la operación principal sigue
// adelante: el error solo queda en el log del servidor.
function registrarBitacora(PDO $db, $accion, $entidad, $entidadId, $descripcion, ?array $cambios = null) {
    try {
        $stmt = $db->prepare("INSERT INTO bitacora_admin
                (usuario_id, usuario_nombre, accion, entidad, entidad_id, descripcion, cambios, ip)
                VALUES (:uid, :unom, :acc, :ent, :eid, :des, :cam, :ip)");
        $stmt->execute([
            ":uid"  => $_SESSION["id"] ?? null,
            ":unom" => $_SESSION["nombre"] ?? null,
            ":acc"  => $accion,
            ":ent"  => $entidad,
            ":eid"  => $entidadId,
            ":des"  => mb_substr($descripcion, 0, 255),
            ":cam"  => $cambios ? json_encode($cambios, JSON_UNESCAPED_UNICODE) : null,
            ":ip"   => $_SERVER["REMOTE_ADDR"] ?? null,
        ]);
    } catch (Exception $e) {
        error_log("bitacora: " . $e->getMessage());
    }
}

// Campos que cambiaron entre dos versiones de un registro: {campo: {antes, despues}}.
// Al crear, $antes es null; al eliminar, $despues es null.
function cambiosEntre(?array $antes, ?array $despues) {
    $cambios = [];
    $campos = array_unique(array_merge(array_keys($antes ?? []), array_keys($despues ?? [])));
    foreach ($campos as $campo) {
        $a = $antes[$campo] ?? null;
        $d = $despues[$campo] ?? null;
        if ((string)$a !== (string)$d) {
            $cambios[$campo] = ["antes" => $a, "despues" => $d];
        }
    }
    return $cambios;
}

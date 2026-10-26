<?php
// Planes de membresía (tabla planes) y límite de usuarios por clínica.

// Planes indexados por código (Basico, Medio, Alto), en su orden
function obtenerPlanes(PDO $db) {
    return $db->query("SELECT codigo, id, nombre, precio_mensual, max_usuarios FROM planes ORDER BY orden, id")
              ->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
}

// 1234567.5 -> "$ 1.234.568" (pesos, sin decimales)
function formatoPesos($valor) {
    return '$ ' . number_format((float)$valor, 0, ',', '.');
}

// Impide superar el límite de usuarios activos del plan de la clínica.
// $excluirId: usuario que se está editando (ya cuenta si pertenece a esa clínica).
function verificarLimiteUsuarios(PDO $db, $clinicaId, $excluirId = null) {
    $stmt = $db->prepare("SELECT c.nombre_clinica, p.nombre AS plan, p.max_usuarios,
            (SELECT COUNT(*) FROM usuarios_clinicas u
              WHERE u.clinica_id = c.id AND u.estado = 1 AND u.id <> :excluir) AS activos
        FROM clientes_clinicas c
        LEFT JOIN planes p ON p.codigo = c.plan_contratado
        WHERE c.id = :clinica");
    $stmt->execute([":clinica" => $clinicaId, ":excluir" => $excluirId ?? 0]);
    $c = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($c && $c["max_usuarios"] !== null && (int)$c["activos"] >= (int)$c["max_usuarios"]) {
        errorValidacion("La clínica " . $c["nombre_clinica"] . " llegó al límite de su plan " . $c["plan"]
            . " (" . (int)$c["max_usuarios"] . " usuarios activos). Cambia el plan o desactiva otro usuario.");
    }
}

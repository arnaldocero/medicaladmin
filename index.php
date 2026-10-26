<?php include "includes/header.php"; ?>
<?php include "includes/menu.php"; ?>
<?php
// Datos del dashboard: estado de las membresías de las clínicas activas
const DIAS_AVISO = 30; // "por vencer" = vence dentro de estos días

require_once "class/Conexion.php";
$db = Conexion::conectar();

$resumen = $db->query("SELECT
        COUNT(*) AS activas,
        SUM(fecha_vencimiento >= CURDATE()) AS vigentes,
        SUM(fecha_vencimiento BETWEEN CURDATE() AND CURDATE() + INTERVAL " . DIAS_AVISO . " DAY) AS por_vencer,
        SUM(fecha_vencimiento < CURDATE() OR fecha_vencimiento IS NULL) AS vencidas
    FROM clientes_clinicas WHERE estado = 1")->fetch(PDO::FETCH_ASSOC);

$usuariosActivos = $db->query("SELECT COUNT(*) FROM usuarios_clinicas u
    INNER JOIN clientes_clinicas c ON u.clinica_id = c.id
    WHERE u.estado = 1 AND c.estado = 1")->fetchColumn();

// Clínicas vencidas o que vencen pronto, las más urgentes primero
$alertas = $db->query("SELECT c.nombre_clinica, c.nit_empresa, c.plan_contratado, c.fecha_vencimiento,
        (SELECT p.nombre FROM planes p WHERE p.codigo = c.plan_contratado) AS plan_nombre,
        DATEDIFF(c.fecha_vencimiento, CURDATE()) AS dias,
        (SELECT COUNT(*) FROM usuarios_clinicas u WHERE u.clinica_id = c.id AND u.estado = 1) AS usuarios
    FROM clientes_clinicas c
    WHERE c.estado = 1
      AND (c.fecha_vencimiento IS NULL OR c.fecha_vencimiento <= CURDATE() + INTERVAL " . DIAS_AVISO . " DAY)
    ORDER BY c.fecha_vencimiento IS NOT NULL, c.fecha_vencimiento ASC")->fetchAll(PDO::FETCH_ASSOC);

require_once "includes/planes.php";

// Por plan: clínicas activas, vigentes e ingreso mensual (solo cuentan las membresías vigentes)
$porPlan = $db->query("SELECT p.codigo, p.nombre, p.precio_mensual,
        COUNT(c.id) AS clinicas,
        COALESCE(SUM(c.fecha_vencimiento >= CURDATE()), 0) AS vigentes,
        COALESCE(SUM(c.fecha_vencimiento >= CURDATE()), 0) * p.precio_mensual AS ingreso
    FROM planes p
    LEFT JOIN clientes_clinicas c ON c.plan_contratado = p.codigo AND c.estado = 1
    GROUP BY p.id ORDER BY p.orden, p.id")->fetchAll(PDO::FETCH_ASSOC);
$ingresoMensual = array_sum(array_column($porPlan, "ingreso"));

// Clínicas activas con más usuarios activos que el límite de su plan
$excedidas = $db->query("SELECT c.nombre_clinica, c.nit_empresa, p.nombre AS plan, p.max_usuarios,
        (SELECT COUNT(*) FROM usuarios_clinicas u WHERE u.clinica_id = c.id AND u.estado = 1) AS usuarios
    FROM clientes_clinicas c
    INNER JOIN planes p ON p.codigo = c.plan_contratado
    WHERE c.estado = 1 AND p.max_usuarios IS NOT NULL
    HAVING usuarios > p.max_usuarios
    ORDER BY usuarios - p.max_usuarios DESC")->fetchAll(PDO::FETCH_ASSOC);

// Los ingresos solo los ve el superadmin
$verIngresos = $_SESSION["rol"] === "superadmin";

function h($texto) { return htmlspecialchars((string)$texto, ENT_QUOTES, "UTF-8"); }

$tarjetas = [
    ["Clínicas activas", (int)$resumen["activas"], "fa-hospital-o", "flat-color-1"],
    ["Membresías vigentes", (int)$resumen["vigentes"], "fa-check-circle", "flat-color-2"],
    ["Por vencer (" . DIAS_AVISO . " días)", (int)$resumen["por_vencer"], "fa-clock-o", "flat-color-3"],
    ["Vencidas", (int)$resumen["vencidas"], "fa-exclamation-triangle", "flat-color-4"],
];
?>

<div id="right-panel" class="right-panel">

<?php include "includes/barra_superior.php"; ?>

    <div class="content">
        <div class="animated fadeIn">

            <div class="row">
                <?php foreach ($tarjetas as [$titulo, $valor, $icono, $color]): ?>
                <div class="col-xl-3 col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="stat-widget-five">
                                <div class="stat-icon dib <?php echo $color; ?>">
                                    <i class="fa <?php echo $icono; ?>"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="text-left dib">
                                        <div class="stat-text"><?php echo $valor; ?></div>
                                        <div class="stat-heading"><?php echo h($titulo); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <strong class="card-title">Membresías vencidas o por vencer</strong>
                        </div>
                        <div class="card-body">
                            <?php if (empty($alertas)): ?>
                                <p class="text-success mb-0"><i class="fa fa-check"></i> Todas las membresías están al día durante los próximos <?php echo DIAS_AVISO; ?> días.</p>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Clínica</th>
                                            <th>Plan</th>
                                            <th>Usuarios</th>
                                            <th>Vence</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($alertas as $c):
                                            $dias = $c["dias"];
                                            if ($dias === null) {
                                                [$badge, $estado] = ["badge-secondary", "Sin fecha"];
                                            } elseif ($dias < 0) {
                                                [$badge, $estado] = ["badge-danger", "Vencida hace " . abs($dias) . " día(s)"];
                                            } elseif ($dias == 0) {
                                                [$badge, $estado] = ["badge-danger", "Vence hoy"];
                                            } else {
                                                [$badge, $estado] = ["badge-warning", "Vence en $dias día(s)"];
                                            }
                                        ?>
                                        <tr>
                                            <td><b><?php echo h($c["nombre_clinica"]); ?></b><br><small class="text-muted"><?php echo h($c["nit_empresa"]); ?></small></td>
                                            <td><?php echo h($c["plan_nombre"] ?? $c["plan_contratado"]); ?></td>
                                            <td><?php echo (int)$c["usuarios"]; ?></td>
                                            <td><?php echo h($c["fecha_vencimiento"] ?? "—"); ?></td>
                                            <td><span class="badge <?php echo $badge; ?>"><?php echo h($estado); ?></span></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($excedidas)): ?>
                    <div class="card border-danger">
                        <div class="card-header bg-danger text-white">
                            <strong class="card-title mb-0"><i class="fa fa-users"></i> Clínicas por encima del límite de usuarios</strong>
                        </div>
                        <div class="card-body">
                            <p class="small text-muted">No pueden crear ni reactivar usuarios hasta bajar del límite o cambiar a un plan mayor.</p>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead>
                                        <tr><th>Clínica</th><th>Plan</th><th>Usuarios activos</th><th>Límite</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($excedidas as $c): ?>
                                        <tr>
                                            <td><b><?php echo h($c["nombre_clinica"]); ?></b><br><small class="text-muted"><?php echo h($c["nit_empresa"]); ?></small></td>
                                            <td><?php echo h($c["plan"]); ?></td>
                                            <td class="text-danger font-weight-bold"><?php echo (int)$c["usuarios"]; ?></td>
                                            <td><?php echo (int)$c["max_usuarios"]; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="col-lg-4">
                    <?php if ($verIngresos): ?>
                    <div class="card">
                        <div class="card-header">
                            <strong class="card-title">Ingresos</strong>
                        </div>
                        <div class="card-body pb-2">
                            <div class="small text-muted">Ingreso mensual (membresías vigentes)</div>
                            <div class="h3 mb-1"><?php echo formatoPesos($ingresoMensual); ?></div>
                            <div class="small text-muted">Proyección anual: <b><?php echo formatoPesos($ingresoMensual * 12); ?></b></div>
                            <?php if ($ingresoMensual == 0): ?>
                            <div class="small text-warning mt-2"><i class="fa fa-info-circle"></i> Configura los precios en <a href="planes.php">Planes</a>.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-header">
                            <strong class="card-title">Resumen por plan</strong>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                Usuarios de clínicas activos <b><?php echo (int)$usuariosActivos; ?></b>
                            </li>
                            <?php foreach ($porPlan as $p): ?>
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    Plan <?php echo h($p["nombre"]); ?>
                                    <b><?php echo (int)$p["clinicas"]; ?> clínica(s)</b>
                                </div>
                                <?php if ($verIngresos): ?>
                                <div class="d-flex justify-content-between small text-muted">
                                    <span><?php echo (int)$p["vigentes"]; ?> vigente(s) × <?php echo formatoPesos($p["precio_mensual"]); ?></span>
                                    <span><?php echo formatoPesos($p["ingreso"]); ?>/mes</span>
                                </div>
                                <?php endif; ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

        </div> </div> <div class="clearfix"></div>

<?php include "includes/footer.php"; ?>

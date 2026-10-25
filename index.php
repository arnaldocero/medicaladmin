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
        DATEDIFF(c.fecha_vencimiento, CURDATE()) AS dias,
        (SELECT COUNT(*) FROM usuarios_clinicas u WHERE u.clinica_id = c.id AND u.estado = 1) AS usuarios
    FROM clientes_clinicas c
    WHERE c.estado = 1
      AND (c.fecha_vencimiento IS NULL OR c.fecha_vencimiento <= CURDATE() + INTERVAL " . DIAS_AVISO . " DAY)
    ORDER BY c.fecha_vencimiento IS NOT NULL, c.fecha_vencimiento ASC")->fetchAll(PDO::FETCH_ASSOC);

$porPlan = $db->query("SELECT plan_contratado, COUNT(*) AS total FROM clientes_clinicas
    WHERE estado = 1 GROUP BY plan_contratado")->fetchAll(PDO::FETCH_KEY_PAIR);

function h($texto) { return htmlspecialchars((string)$texto, ENT_QUOTES, "UTF-8"); }

$tarjetas = [
    ["Clínicas activas", (int)$resumen["activas"], "fa-hospital-o", "flat-color-1"],
    ["Membresías vigentes", (int)$resumen["vigentes"], "fa-check-circle", "flat-color-2"],
    ["Por vencer (" . DIAS_AVISO . " días)", (int)$resumen["por_vencer"], "fa-clock-o", "flat-color-3"],
    ["Vencidas", (int)$resumen["vencidas"], "fa-exclamation-triangle", "flat-color-4"],
];
?>

<div id="right-panel" class="right-panel">

    <header id="header" class="header">
        <div class="top-left">
            <div class="navbar-header">
                <a class="navbar-brand" href="./">SISTEMA CLÍNICO</a>
                <a id="menuToggle" class="menutoggle"><i class="fa fa-bars"></i></a>
            </div>
        </div>
        <div class="top-right">
            <div class="header-menu">
                <div class="user-area dropdown float-right">
                    <a href="#" class="dropdown-toggle active" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="mr-2"><?php echo htmlspecialchars($_SESSION["nombre"], ENT_QUOTES, "UTF-8"); ?></span>
                        <img class="user-avatar rounded-circle" src="images/admin.jpg" alt="User Avatar">
                    </a>
                    <div class="user-menu dropdown-menu">
                        <a class="nav-link" href="salir.php"><i class="fa fa-power-off"></i>Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

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
                                            <td><?php echo h($c["plan_contratado"]); ?></td>
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
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <strong class="card-title">Resumen</strong>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                Usuarios de clínicas activos <b><?php echo (int)$usuariosActivos; ?></b>
                            </li>
                            <?php foreach (["Basico" => "Plan Básico", "Medio" => "Plan Medio", "Alto" => "Plan Alto"] as $plan => $etiqueta): ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <?php echo $etiqueta; ?> <b><?php echo (int)($porPlan[$plan] ?? 0); ?></b>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

        </div> </div> <div class="clearfix"></div>

<?php include "includes/footer.php"; ?>

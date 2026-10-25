<?php
// Marca como activo el enlace de la página actual
$paginaActual = basename($_SERVER["PHP_SELF"]);
function claseActiva($pagina) {
    global $paginaActual;
    return $paginaActual === $pagina ? ' class="active"' : '';
}
?>
<aside id="left-panel" class="left-panel">
    <nav class="navbar navbar-expand-sm navbar-default">
        <div id="main-menu" class="main-menu collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li<?php echo claseActiva("index.php"); ?>>
                    <a href="index.php"><i class="menu-icon fa fa-laptop"></i>Dashboard </a>
                </li>

                <li class="menu-title">Gestión de Clientes</li>
                <?php if($_SESSION["rol"] == "superadmin"): ?>
                <li<?php echo claseActiva("membresias.php"); ?>>
                    <a href="membresias.php"> <i class="menu-icon fa fa-id-card"></i>Membresías / Clínicas</a>
                </li>
                <?php endif; ?>
                <li<?php echo claseActiva("usuarios_clinicas.php"); ?>>
                    <a href="usuarios_clinicas.php"> <i class="menu-icon fa fa-users"></i>Usuarios Hospitalarios</a>
                </li>

                <?php if($_SESSION["rol"] == "superadmin"): ?>
                <li class="menu-title">Configuración Maestra</li>
                <li<?php echo claseActiva("usuarios.php"); ?>>
                    <a href="usuarios.php"> <i class="menu-icon fa fa-user-secret"></i>Personal del Panel</a>
                </li>
                <li<?php echo claseActiva("bitacora.php"); ?>>
                    <a href="bitacora.php"> <i class="menu-icon fa fa-history"></i>Bitácora</a>
                </li>
                <?php endif; ?>

                <li class="menu-title">Sesión</li>
                <li>
                    <a href="salir.php"> <i class="menu-icon fa fa-power-off"></i>Cerrar Sesión </a>
                </li>
            </ul>
        </div>
    </nav>
</aside>

<aside id="left-panel" class="left-panel">
    <nav class="navbar navbar-expand-sm navbar-default">
        <div id="main-menu" class="main-menu collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li class="active">
                    <a href="index.php"><i class="menu-icon fa fa-laptop"></i>Dashboard </a>
                </li>
                
                <li class="menu-title">Gestión de Clientes</li>
                <?php if($_SESSION["rol"] == "superadmin"): ?>
                <li>
                    <a href="membresias.php"> <i class="menu-icon fa fa-id-card"></i>Membresías / Clínicas</a>
                </li>
                <?php endif; ?>
                <li>
                    <a href="usuarios_clinicas.php"> <i class="menu-icon fa fa-users"></i>Usuarios Hospitalarios</a>
                </li>

                <?php if($_SESSION["rol"] == "superadmin"): ?>
                <li class="menu-title">Configuración Maestra</li>
                <li>
                    <a href="usuarios.php"> <i class="menu-icon fa fa-user-secret"></i>Personal del Panel</a>
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
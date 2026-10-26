<?php
// Barra superior común: botón del menú (móvil), nombre y rol del usuario, y cerrar sesión.
// Se incluye justo después de abrir <div id="right-panel">.
$rolesVisibles = ["superadmin" => "Super administrador", "admin" => "Administrador"];
?>
    <header id="header" class="header">
        <div class="top-left">
            <div class="navbar-header">
                <a class="navbar-brand" href="index.php">SISTEMA CLÍNICO</a>
                <a id="menuToggle" class="menutoggle" role="button" tabindex="0" aria-label="Mostrar u ocultar menú"><i class="fa fa-bars"></i></a>
            </div>
        </div>
        <div class="top-right">
            <div class="header-menu">
                <div class="user-area dropdown float-right">
                    <a href="#" class="dropdown-toggle active" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="mr-2 d-none d-sm-inline"><?php echo htmlspecialchars($_SESSION["nombre"], ENT_QUOTES, "UTF-8"); ?></span>
                        <img class="user-avatar rounded-circle" src="images/admin.jpg" alt="Usuario">
                    </a>
                    <div class="user-menu dropdown-menu">
                        <span class="dropdown-item-text small text-muted px-3">
                            <b class="d-block text-dark"><?php echo htmlspecialchars($_SESSION["nombre"], ENT_QUOTES, "UTF-8"); ?></b>
                            <?php echo $rolesVisibles[$_SESSION["rol"]] ?? htmlspecialchars($_SESSION["rol"], ENT_QUOTES, "UTF-8"); ?>
                        </span>
                        <div class="dropdown-divider"></div>
                        <a class="nav-link" href="salir.php"><i class="fa fa-power-off"></i> Cerrar sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

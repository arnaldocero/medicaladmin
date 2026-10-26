<?php
// Barra superior común: botón del menú (móvil), nombre y rol del usuario, perfil y cerrar sesión.
// Se incluye justo después de abrir <div id="right-panel">.
$rolesVisibles = ["superadmin" => "Super administrador", "admin" => "Administrador"];

// Avatar: inicial del nombre en mayúscula sobre un color fijo para cada nombre
$nombreUsuario = trim($_SESSION["nombre"] ?? "");
$inicial = $nombreUsuario !== "" ? mb_strtoupper(mb_substr($nombreUsuario, 0, 1, "UTF-8"), "UTF-8") : "?";
$coloresAvatar = ["#1abc9c", "#3498db", "#9b59b6", "#e67e22", "#e74c3c", "#16a085", "#2980b9", "#8e44ad", "#d35400", "#2c3e50"];
$colorAvatar = $coloresAvatar[crc32($nombreUsuario) % count($coloresAvatar)];
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
                        <span class="mr-2 d-none d-sm-inline"><?php echo htmlspecialchars($nombreUsuario, ENT_QUOTES, "UTF-8"); ?></span>
                        <span class="avatar-inicial" style="background: <?php echo $colorAvatar; ?>" aria-hidden="true"><?php echo htmlspecialchars($inicial, ENT_QUOTES, "UTF-8"); ?></span>
                    </a>
                    <div class="user-menu dropdown-menu">
                        <span class="dropdown-item-text small text-muted px-3">
                            <b class="d-block text-dark"><?php echo htmlspecialchars($nombreUsuario, ENT_QUOTES, "UTF-8"); ?></b>
                            <?php echo $rolesVisibles[$_SESSION["rol"]] ?? htmlspecialchars($_SESSION["rol"], ENT_QUOTES, "UTF-8"); ?>
                        </span>
                        <div class="dropdown-divider"></div>
                        <a class="nav-link" href="perfil.php"><i class="fa fa-user"></i> Mi perfil</a>
                        <a class="nav-link" href="salir.php"><i class="fa fa-power-off"></i> Cerrar sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

<?php
// Sesión válida, no inactiva y con usuario activo; las páginas restringidas definen $rolRequerido antes
require_once __DIR__ . "/sesion.php";
verificarSesion(!empty($rolRequerido) ? [$rolRequerido] : ROLES_PANEL);
?>
<!doctype html>
<html class="no-js" lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Administrador - Clínica</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo $_SESSION["csrf_token"]; ?>">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/normalize.css@8.0.0/normalize.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-bs4@1.13.8/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lykmapipo/themify-icons@0.1.2/css/themify-icons.css">
    <link rel="stylesheet" href="assets/css/cs-skin-elastic.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        /* Las columnas flex no se encogen por debajo del ancho de una tabla ancha y ensanchan
           toda la página en el móvil; con min-width 0 la tabla se desplaza dentro de .table-responsive */
        .row > [class*="col"] { min-width: 0; }
        /* La plantilla pone body { display: table }, que crece hasta el ancho de su contenido:
           en pantallas pequeñas una tabla ancha estiraba toda la página fuera de la pantalla */
        @media (max-width: 991px) { body { display: block; } }

        /* Menú del usuario en la barra superior (la plantilla fija 150px de ancho y line-height 55px) */
        .user-area .user-menu { min-width: 220px; line-height: 1.4; }
        .user-area .user-menu .dropdown-item-text { padding: 8px 12px; white-space: normal; }
        .user-area .user-menu .nav-link { line-height: 1.4; padding: 8px 12px; }
    </style>
</head>
<body>
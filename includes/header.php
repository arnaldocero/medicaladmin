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
        #weatherWidget .currentDesc { color: #ffffff!important; }
        .traffic-chart { min-height: 335px; }
        #flotPie1 { height: 150px; }
        #flotPie1 td { padding:3px; }
        #flotPie1 table { top: 20px!important; right: -10px!important; }
        .chart-container { display: table; min-width: 270px; text-align: left; padding-top: 10px; padding-bottom: 10px; }
    </style>
</head>
<body>
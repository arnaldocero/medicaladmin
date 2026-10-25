<?php 
session_start(); 
// Si la variable de sesión 'validar' no existe o no es 'ok', lo mandamos al login
if(!isset($_SESSION["validar"]) || $_SESSION["validar"] != "ok"){
    header("location: login.php");
    exit();
}

// Páginas restringidas: definen $rolRequerido antes de incluir este archivo
if (!empty($rolRequerido) && ($_SESSION["rol"] ?? '') !== $rolRequerido) {
    header("location: index.php");
    exit();
}

// Token CSRF de la sesión: utils.js lo envía en cada petición AJAX
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
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
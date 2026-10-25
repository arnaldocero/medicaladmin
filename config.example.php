<?php
// Plantilla de configuración. Copia este archivo como "config.php" en cada
// entorno (local y servidor) y pon ahí los datos reales.
// config.php está en .gitignore: nunca se sube al repositorio.
return [
    'db_host' => 'localhost',
    'db_name' => 'nombre_base_de_datos',
    'db_user' => 'usuario',
    'db_pass' => 'contraseña',

    // true solo en local: muestra el detalle de los errores de conexión
    'debug'   => false,
];

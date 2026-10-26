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

    // URL pública del panel, sin "/" final. Se usa en los enlaces de los correos
    // (recuperación de contraseña). Ej.: https://midominio.com/HMSadmin
    'url_base' => 'https://midominio.com/HMSadmin',

    // Correos: 'mail' envía con mail() de PHP (cPanel); 'archivo' los guarda en logs/correos (local)
    'correo_modo'      => 'mail',
    // Remitente: debe ser una cuenta del mismo dominio (créala en cPanel → Cuentas de correo),
    // si no, los correos suelen acabar en spam o ser rechazados
    'correo_remitente' => 'no-responder@midominio.com',
    'correo_nombre'    => 'Sistema Clínico',

    // Avisos de membresías por vencer (cron/avisos_vencimiento.php):
    // días antes del vencimiento en que se avisa (0 = el mismo día)
    'avisos_dias'       => [30, 15, 7, 1, 0],
    // false: solo se envía el resumen diario a los superadmins.
    // true: además se avisa a cada clínica en su email de contacto.
    'avisos_a_clinicas' => false,
];

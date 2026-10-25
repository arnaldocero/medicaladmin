-- Registro de intentos de login del panel administrativo (usuarios_admin).
-- Se usa para bloquear temporalmente tras varios intentos fallidos.
-- Ejecutar una vez en cada base de datos (local y servidor) desde phpMyAdmin.
CREATE TABLE IF NOT EXISTS `login_intentos_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `exitoso` tinyint(1) NOT NULL DEFAULT 0,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_email_fecha` (`email`, `fecha`),
  KEY `idx_ip_fecha` (`ip`, `fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

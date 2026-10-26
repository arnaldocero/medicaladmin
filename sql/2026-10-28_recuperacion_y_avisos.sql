-- Recuperación de contraseña del panel y registro de avisos de vencimiento enviados.
-- Ejecutar una vez en cada base de datos (local y servidor) desde phpMyAdmin.

-- Enlaces de recuperación: se guarda solo el hash SHA-256 del token, nunca el token.
CREATE TABLE IF NOT EXISTS `recuperacion_password` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,      -- NULL si el email no existe (cuenta para el límite de solicitudes)
  `email` varchar(100) NOT NULL,
  `token_hash` char(64) DEFAULT NULL,
  `ip` varchar(45) NOT NULL,
  `expira` datetime DEFAULT NULL,
  `usado` tinyint(1) NOT NULL DEFAULT 0,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_hash` (`token_hash`),
  KEY `idx_email_fecha` (`email`, `fecha`),
  KEY `idx_ip_fecha` (`ip`, `fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Avisos de vencimiento ya enviados, para no repetir el mismo aviso
-- (clínica + fecha de vencimiento + días de antelación + destinatario).
CREATE TABLE IF NOT EXISTS `avisos_vencimiento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clinica_id` int(11) NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `dias_antes` int(11) NOT NULL,
  `destino` varchar(20) NOT NULL,          -- clinica | superadmins
  `email` varchar(100) DEFAULT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `aviso_unico` (`clinica_id`, `fecha_vencimiento`, `dias_antes`, `destino`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

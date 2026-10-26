-- Bitácora de acciones del panel administrativo: quién hizo qué, sobre qué y cuándo.
-- Ejecutar una vez en cada base de datos (local y servidor) desde phpMyAdmin.
CREATE TABLE IF NOT EXISTS `bitacora_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,           -- usuarios_admin.id de quien hizo la acción
  `usuario_nombre` varchar(100) DEFAULT NULL,  -- copia del nombre, por si el usuario se modifica después
  `accion` varchar(20) NOT NULL,               -- crear | editar | eliminar | desactivar | activar | login
  `entidad` varchar(30) NOT NULL,              -- clinica | usuario_clinica | usuario_admin | sesion
  `entidad_id` int(11) DEFAULT NULL,
  `descripcion` varchar(255) NOT NULL,
  `cambios` text DEFAULT NULL,                 -- JSON {campo: {antes, despues}}; nunca contraseñas
  `ip` varchar(45) DEFAULT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_fecha` (`fecha`),
  KEY `idx_entidad` (`entidad`, `entidad_id`),
  KEY `idx_usuario` (`usuario_id`, `fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Planes de membresía: precio mensual y límite de usuarios por clínica.
-- "codigo" coincide con clientes_clinicas.plan_contratado (Basico / Medio / Alto),
-- así no se modifica la tabla de clínicas que también usa la app HMS.
-- Los precios empiezan en 0 y el límite vacío (ilimitado): se ajustan desde la página Planes.
-- Ejecutar una vez en cada base de datos (local y servidor) desde phpMyAdmin.
CREATE TABLE IF NOT EXISTS `planes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `precio_mensual` decimal(12,2) NOT NULL DEFAULT 0.00,
  `max_usuarios` int(11) DEFAULT NULL,         -- NULL = sin límite
  `orden` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `planes` (`codigo`, `nombre`, `precio_mensual`, `max_usuarios`, `orden`) VALUES
  ('Basico', 'Básico', 0, NULL, 1),
  ('Medio',  'Medio',  0, NULL, 2),
  ('Alto',   'Alto',   0, NULL, 3);

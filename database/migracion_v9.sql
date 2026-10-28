-- Migración para la base de datos YA EXISTENTE (conserva todos los datos).
-- Ejecutar una sola vez en phpMyAdmin > bodega_municipal > pestaña SQL.

-- 1) Cédula y celular de quien solicita el préstamo/salida
ALTER TABLE `salidas`
  ADD COLUMN `cedula_quien_recibe` varchar(20) NOT NULL DEFAULT '' AFTER `nombre_quien_recibe`,
  ADD COLUMN `celular_quien_recibe` varchar(20) NOT NULL DEFAULT '' AFTER `cedula_quien_recibe`;

-- 2) Permisos extra que concede el Administrador
CREATE TABLE IF NOT EXISTS `permisos_extra` (
  `id_usuario` int(11) NOT NULL,
  `permiso` enum('entradas','salidas') NOT NULL,
  `fecha_asignacion` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_usuario`,`permiso`),
  CONSTRAINT `permisos_extra_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

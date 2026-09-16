-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Xerado en: 16 de Set de 2026 ás 20:18
-- Versión do servidor: 10.4.32-MariaDB
-- Versión do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `bodega_municipal`
--

-- --------------------------------------------------------

--
-- Estrutura da táboa `categorias`
--

CREATE TABLE `categorias` (
  `codigo_categoria` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A extraer os datos da táboa `categorias`
--

INSERT INTO `categorias` (`codigo_categoria`, `descripcion`) VALUES
('herramientas_electricas', 'Equipos eléctricos y electromecánicos portátiles'),
('herramientas_manuales', 'Herramientas de uso manual para construcción y mantenimiento'),
('limpieza', 'Productos de aseo, desinfección y limpieza general'),
('maquinaria_pesada', 'Equipos pesados y maquinaria mayor para obras'),
('seguridad_higiene', 'Equipos de protección personal (EPP) y seguridad industrial');

-- --------------------------------------------------------

--
-- Estrutura da táboa `destinos`
--

CREATE TABLE `destinos` (
  `id_destino` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `tipo` enum('dependencia','escuela','obra','otro') NOT NULL DEFAULT 'dependencia',
  `responsable` varchar(100) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A extraer os datos da táboa `destinos`
--

INSERT INTO `destinos` (`id_destino`, `nombre`, `tipo`, `responsable`, `activo`) VALUES
(1, 'Alcaldía - Área de Limpieza', 'dependencia', 'Ana López', 1),
(2, 'Escuela Municipal Centro', 'escuela', 'Prof. Roberto Méndez', 1),
(3, 'Escuela Municipal Norte', 'escuela', 'Prof. Laura Vargas', 1),
(4, 'Obra Calle Principal', 'obra', 'Ing. Carlos Ruiz', 1),
(5, 'Obra Parque Central', 'obra', 'Ing. Sofía Herrera', 1),
(6, 'Área de Mantenimiento', 'dependencia', 'Pedro Morales', 1),
(7, 'Mercado Municipal', 'otro', 'Luis Ramírez', 1);

-- --------------------------------------------------------

--
-- Estrutura da táboa `detalle_entradas`
--

CREATE TABLE `detalle_entradas` (
  `id_detalle` int(11) NOT NULL,
  `id_entrada` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da táboa `detalle_salidas`
--

CREATE TABLE `detalle_salidas` (
  `id_detalle` int(11) NOT NULL,
  `id_salida` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `cantidad_devuelta` int(11) NOT NULL DEFAULT 0,
  `estado_item` enum('bueno','dañado','perdido') DEFAULT 'bueno'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da táboa `entradas`
--

CREATE TABLE `entradas` (
  `id_entrada` int(11) NOT NULL,
  `numero_voucher` varchar(50) NOT NULL,
  `tipo_entrada` enum('abastecimiento','devolucion') NOT NULL DEFAULT 'abastecimiento',
  `nombre_quien_entrega` varchar(150) NOT NULL,
  `id_salida_origen` int(11) DEFAULT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da táboa `productos`
--

CREATE TABLE `productos` (
  `id_producto` int(11) NOT NULL,
  `codigo_categoria` varchar(50) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `tipo` enum('consumible','herramienta') NOT NULL,
  `unidad` varchar(30) NOT NULL DEFAULT 'unidad',
  `stock_actual` int(11) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A extraer os datos da táboa `productos`
--

INSERT INTO `productos` (`id_producto`, `codigo_categoria`, `codigo`, `nombre`, `tipo`, `unidad`, `stock_actual`, `activo`) VALUES
(1, 'limpieza', 'LIM-001', 'Cloro', 'consumible', 'galón', 120, 1),
(2, 'limpieza', 'LIM-002', 'Desinfectante multiusos', 'consumible', 'galón', 80, 1),
(3, 'limpieza', 'LIM-003', 'Jabón líquido antibacterial', 'consumible', 'galón', 90, 1),
(4, 'limpieza', 'LIM-004', 'Detergente en polvo', 'consumible', 'kg', 150, 1),
(5, 'limpieza', 'LIM-005', 'Limpiavidrios', 'consumible', 'galón', 50, 1),
(6, 'herramientas_manuales', 'HER-001', 'Pala redonda', 'herramienta', 'unidad', 35, 1),
(7, 'herramientas_manuales', 'HER-002', 'Pico', 'herramienta', 'unidad', 22, 1),
(8, 'herramientas_manuales', 'HER-003', 'Martillo de uña', 'herramienta', 'unidad', 28, 1),
(9, 'herramientas_manuales', 'HER-004', 'Machete', 'herramienta', 'unidad', 25, 1),
(10, 'herramientas_manuales', 'HER-005', 'Cinta métrica 8m', 'herramienta', 'unidad', 18, 1),
(11, 'herramientas_electricas', 'ELE-001', 'Taladro percutor 1/2\"', 'herramienta', 'unidad', 10, 1),
(12, 'herramientas_electricas', 'ELE-002', 'Amoladora angular 4.5\"', 'herramienta', 'unidad', 9, 1),
(13, 'herramientas_electricas', 'ELE-003', 'Sierra circular 7.1/4\"', 'herramienta', 'unidad', 6, 1),
(14, 'herramientas_electricas', 'ELE-004', 'Lijadora orbital', 'herramienta', 'unidad', 7, 1),
(15, 'herramientas_electricas', 'ELE-005', 'Atornillador eléctrico', 'herramienta', 'unidad', 8, 1),
(16, 'maquinaria_pesada', 'MAQ-001', 'Cortadora de concreto 14\"', 'herramienta', 'unidad', 4, 1),
(17, 'maquinaria_pesada', 'MAQ-002', 'Compactadora bailarina', 'herramienta', 'unidad', 5, 1),
(18, 'maquinaria_pesada', 'MAQ-003', 'Mezcladora de concreto 1 saco', 'herramienta', 'unidad', 3, 1),
(19, 'maquinaria_pesada', 'MAQ-004', 'Generador eléctrico 5KVA', 'herramienta', 'unidad', 4, 1),
(20, 'maquinaria_pesada', 'MAQ-005', 'Motosierra 20\"', 'herramienta', 'unidad', 4, 1),
(21, 'seguridad_higiene', 'SEG-001', 'Casco de seguridad', 'consumible', 'unidad', 50, 1),
(22, 'seguridad_higiene', 'SEG-002', 'Chaleco reflectivo', 'consumible', 'unidad', 40, 1),
(23, 'seguridad_higiene', 'SEG-003', 'Guantes de carnaza', 'consumible', 'par', 60, 1),
(24, 'seguridad_higiene', 'SEG-004', 'Lentes de seguridad', 'consumible', 'unidad', 45, 1),
(25, 'seguridad_higiene', 'SEG-005', 'Botas de seguridad', 'consumible', 'par', 30, 1);

-- --------------------------------------------------------

--
-- Estrutura da táboa `salidas`
--

CREATE TABLE `salidas` (
  `id_salida` int(11) NOT NULL,
  `numero_documento` varchar(50) NOT NULL,
  `tipo` enum('consumo','prestamo') NOT NULL,
  `motivo_salida` varchar(255) NOT NULL,
  `id_destino` int(11) NOT NULL,
  `nombre_quien_entrega` varchar(150) NOT NULL,
  `nombre_quien_recibe` varchar(150) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha_salida` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_prevista_devolucion` date DEFAULT NULL,
  `estado` enum('entregado','parcial','devuelto','vencido') NOT NULL DEFAULT 'entregado',
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da táboa `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `nombre_usuario` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('Administrador','Operador_Entradas','Operador_Salidas') NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A extraer os datos da táboa `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `cedula`, `nombre_usuario`, `password`, `rol`, `activo`, `fecha_creacion`) VALUES
(1, '001-123456-0001A', 'Carlos Admin', 'admin123', 'Administrador', 1, '2026-09-15 03:31:42'),
(2, '001-654321-0002B', 'María Entradas', 'entrada123', 'Operador_Entradas', 1, '2026-09-15 03:31:42'),
(3, '001-987654-0003C', 'Juan Salidas', 'salida123', 'Operador_Salidas', 1, '2026-09-15 03:31:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`codigo_categoria`);

--
-- Indexes for table `destinos`
--
ALTER TABLE `destinos`
  ADD PRIMARY KEY (`id_destino`);

--
-- Indexes for table `detalle_entradas`
--
ALTER TABLE `detalle_entradas`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `id_entrada` (`id_entrada`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indexes for table `detalle_salidas`
--
ALTER TABLE `detalle_salidas`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `id_salida` (`id_salida`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indexes for table `entradas`
--
ALTER TABLE `entradas`
  ADD PRIMARY KEY (`id_entrada`),
  ADD UNIQUE KEY `numero_voucher` (`numero_voucher`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `fk_salida_origen` (`id_salida_origen`);

--
-- Indexes for table `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `codigo_categoria` (`codigo_categoria`);

--
-- Indexes for table `salidas`
--
ALTER TABLE `salidas`
  ADD PRIMARY KEY (`id_salida`),
  ADD UNIQUE KEY `numero_documento` (`numero_documento`),
  ADD KEY `id_destino` (`id_destino`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `cedula` (`cedula`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `destinos`
--
ALTER TABLE `destinos`
  MODIFY `id_destino` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `detalle_entradas`
--
ALTER TABLE `detalle_entradas`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `detalle_salidas`
--
ALTER TABLE `detalle_salidas`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entradas`
--
ALTER TABLE `entradas`
  MODIFY `id_entrada` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `salidas`
--
ALTER TABLE `salidas`
  MODIFY `id_salida` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricións para os envorcados das táboas
--

--
-- Restricións para a táboa `detalle_entradas`
--
ALTER TABLE `detalle_entradas`
  ADD CONSTRAINT `detalle_entradas_ibfk_1` FOREIGN KEY (`id_entrada`) REFERENCES `entradas` (`id_entrada`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_entradas_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`);

--
-- Restricións para a táboa `detalle_salidas`
--
ALTER TABLE `detalle_salidas`
  ADD CONSTRAINT `detalle_salidas_ibfk_1` FOREIGN KEY (`id_salida`) REFERENCES `salidas` (`id_salida`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_salidas_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`);

--
-- Restricións para a táboa `entradas`
--
ALTER TABLE `entradas`
  ADD CONSTRAINT `entradas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `fk_salida_origen` FOREIGN KEY (`id_salida_origen`) REFERENCES `salidas` (`id_salida`) ON DELETE SET NULL;

--
-- Restricións para a táboa `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`codigo_categoria`) REFERENCES `categorias` (`codigo_categoria`);

--
-- Restricións para a táboa `salidas`
--
ALTER TABLE `salidas`
  ADD CONSTRAINT `salidas_ibfk_1` FOREIGN KEY (`id_destino`) REFERENCES `destinos` (`id_destino`),
  ADD CONSTRAINT `salidas_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

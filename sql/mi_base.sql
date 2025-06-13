-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3307
-- Tiempo de generación: 13-06-2025 a las 04:15:49
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `mi_base`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'Cuidado Facial', 'Productos para la limpieza, hidratación y tratamiento del rostro.', '2025-06-10 01:54:57', '2025-06-10 01:54:57'),
(2, 'Maquillaje', 'Cosméticos para embellecer el rostro y cuerpo.', '2025-06-10 01:54:57', '2025-06-10 01:54:57'),
(3, 'Cuidado Corporal', 'Productos para la hidratación, limpieza y tratamiento de la piel del cuerpo.', '2025-06-10 01:54:57', '2025-06-10 01:54:57'),
(4, 'Cuidado del Cabello', 'Shampoos, acondicionadores, tratamientos y productos de estilizado para el cabello.', '2025-06-10 01:54:57', '2025-06-10 01:54:57'),
(5, 'Perfumes y Fragancias', 'Colonias, perfumes y esencias aromáticas.', '2025-06-10 01:54:57', '2025-06-10 01:54:57'),
(6, 'Accesorios de Belleza', 'Herramientas y complementos para la aplicación de productos de belleza y cuidado personal.', '2025-06-10 01:54:57', '2025-06-10 01:54:57'),
(7, 'Kits y Sets de Regalo', 'Conjuntos de productos de belleza ideales para regalar.', '2025-06-10 01:54:57', '2025-06-10 01:54:57');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas`
--

CREATE TABLE `citas` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `empleado_id` int(11) DEFAULT NULL,
  `servicio_id` int(11) NOT NULL,
  `fecha_hora_cita` datetime NOT NULL,
  `duracion_estimada_min` int(11) DEFAULT 30,
  `fecha_hora_fin` datetime DEFAULT NULL COMMENT 'Fecha y hora de finalización calculada de la cita',
  `estado_cita` varchar(50) NOT NULL DEFAULT 'Pendiente',
  `notas_cliente` text DEFAULT NULL,
  `notas_empleado` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `citas`
--

INSERT INTO `citas` (`id`, `cliente_id`, `empleado_id`, `servicio_id`, `fecha_hora_cita`, `duracion_estimada_min`, `fecha_hora_fin`, `estado_cita`, `notas_cliente`, `notas_empleado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(12, 16, 11, 1, '2025-06-16 17:00:00', 30, NULL, 'Confirmada', '', '', '2025-06-13 00:28:30', '2025-06-13 00:39:42'),
(13, 16, 11, 1, '2025-06-16 16:00:00', 30, NULL, '1', 'prueba', NULL, '2025-06-13 01:24:11', '2025-06-13 01:24:11');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleado_horarios_recurrentes`
--

CREATE TABLE `empleado_horarios_recurrentes` (
  `id` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL,
  `dia_semana` tinyint(4) NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `fecha_desde` date DEFAULT NULL,
  `fecha_hasta` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empleado_horarios_recurrentes`
--

INSERT INTO `empleado_horarios_recurrentes` (`id`, `empleado_id`, `dia_semana`, `hora_inicio`, `hora_fin`, `fecha_desde`, `fecha_hasta`) VALUES
(9, 11, 1, '07:00:00', '19:00:00', '2025-06-09', '2025-06-30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleado_horario_excepciones`
--

CREATE TABLE `empleado_horario_excepciones` (
  `id` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora_inicio` time DEFAULT NULL,
  `hora_fin` time DEFAULT NULL,
  `esta_disponible` tinyint(1) NOT NULL DEFAULT 1,
  `tipo_excepcion` enum('NO_DISPONIBLE','DISPONIBLE_EXTRA') NOT NULL DEFAULT 'NO_DISPONIBLE',
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleado_servicios`
--

CREATE TABLE `empleado_servicios` (
  `empleado_id` int(11) NOT NULL,
  `servicio_id` int(11) NOT NULL,
  `fecha_asignacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empleado_servicios`
--

INSERT INTO `empleado_servicios` (`empleado_id`, `servicio_id`, `fecha_asignacion`) VALUES
(11, 1, '2025-06-11 04:17:46'),
(11, 2, '2025-06-11 04:17:46'),
(11, 3, '2025-06-11 04:17:46'),
(11, 4, '2025-06-11 04:17:46'),
(11, 5, '2025-06-11 04:17:46'),
(11, 6, '2025-06-11 04:17:46'),
(11, 9, '2025-06-12 18:14:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas`
--

CREATE TABLE `facturas` (
  `id` int(11) NOT NULL,
  `cita_id` int(11) NOT NULL,
  `numero_factura` varchar(50) NOT NULL,
  `fecha_emision` datetime NOT NULL DEFAULT current_timestamp(),
  `cliente_id` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL,
  `servicio_nombre_en_factura` varchar(255) NOT NULL,
  `monto_total` decimal(10,2) NOT NULL,
  `estado_factura` varchar(50) NOT NULL DEFAULT 'Pendiente',
  `fecha_pago` datetime DEFAULT NULL,
  `metodo_pago` varchar(50) DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `facturas`
--

INSERT INTO `facturas` (`id`, `cita_id`, `numero_factura`, `fecha_emision`, `cliente_id`, `empleado_id`, `servicio_nombre_en_factura`, `monto_total`, `estado_factura`, `fecha_pago`, `metodo_pago`, `notas`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(13, 12, 'FACT-20250613-FF79', '2025-06-12 19:29:18', 16, 11, 'Corte de Cabello - Dama', 45000.00, 'Pagada', NULL, NULL, NULL, '2025-06-12 19:29:18', '2025-06-12 19:38:10'),
(14, 12, 'FACT-20250613-9DC5', '2025-06-12 19:39:42', 16, 11, 'Corte de Cabello - Dama', 45000.00, 'Pendiente', NULL, NULL, NULL, '2025-06-12 19:39:42', '2025-06-12 19:39:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL,
  `usuario_id_destino` int(11) NOT NULL,
  `mensaje` text NOT NULL,
  `tipo` varchar(50) DEFAULT 'info',
  `enlace` varchar(255) DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_lectura` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id`, `usuario_id_destino`, `mensaje`, `tipo`, `enlace`, `fecha_creacion`, `fecha_lectura`) VALUES
(30, 16, '¡Buenas noticias! Tu cita para Corte de Cabello - Dama el 16/06/2025 a las 17:00 ha sido CONFIRMADA.', 'CITA_CONFIRMADA', 'http://localhost/Proyecto-clip/public/mis-citas', '2025-06-12 19:39:42', NULL),
(31, 11, 'Nueva cita asignada: Por cliente prueba para el servicio \'Corte de Cabello - Dama\' el 16/06/2025 a las 04:00 PM.', 'nueva_cita', 'http://localhost/Proyecto-clip/public/empleado/agenda?fecha_inicio=2025-06-16', '2025-06-12 20:24:11', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock` int(11) NOT NULL DEFAULT 0,
  `categoria_id` int(11) DEFAULT NULL,
  `imagen_url` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0=Inactivo, 1=Activo',
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `descripcion`, `precio`, `stock`, `categoria_id`, `imagen_url`, `activo`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(2, 'Shampoo Head & Shoulder', 'Cont. 180ML', 20000.00, 15, 4, 'prod_6847d6d5a7d931.78277466.png', 1, '2025-06-10 01:55:17', '2025-06-12 20:08:44');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `nombre_rol` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre_rol`, `descripcion`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'admin', 'Administrador con todos los permisos', '2025-06-09 18:32:00', '2025-06-09 18:32:00'),
(2, 'cliente', 'Cliente que puede solicitar citas y ver su historial', '2025-06-09 18:32:00', '2025-06-09 18:32:00'),
(3, 'empleado', 'Empleado que puede gestionar citas y servicios asignados', '2025-06-09 18:32:00', '2025-06-09 18:32:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios`
--

CREATE TABLE `servicios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 para activo, 0 para inactivo',
  `duracion_minutos` int(11) DEFAULT NULL COMMENT 'Duración del servicio en minutos',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `servicios`
--

INSERT INTO `servicios` (`id`, `nombre`, `descripcion`, `precio`, `activo`, `duracion_minutos`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'Corte de Cabello - Dama', 'Corte moderno y estilizado para damas, incluye lavado y secado básico.', 45000.00, 1, 60, '2025-06-10 19:43:49', '2025-06-11 06:16:35'),
(2, 'Corte de Cabello - Caballero', 'Corte clásico o moderno para caballeros, incluye perfilado de barba si se desea.', 35000.00, 1, 45, '2025-06-10 19:43:50', '2025-06-10 19:58:57'),
(3, 'Tintura Completa', 'Aplicación de color en todo el cabello. El precio puede variar según el largo y cantidad de producto.', 120000.00, 1, 120, '2025-06-10 19:43:50', '2025-06-10 19:43:50'),
(4, 'Manicura Clásica', 'Limado, limpieza de cutículas y esmaltado tradicional.', 25000.00, 1, 40, '2025-06-10 19:43:50', '2025-06-10 19:43:50'),
(5, 'Pedicura Spa', 'Pedicura completa con exfoliación, hidratación y masaje.', 45000.00, 1, 75, '2025-06-10 19:43:50', '2025-06-10 19:43:50'),
(6, 'Peinado para Eventos', 'Peinado especial para bodas, fiestas u otras ocasiones. Incluye preparación del cabello.', 80000.00, 1, 90, '2025-06-10 19:43:50', '2025-06-10 19:43:50'),
(7, 'Tratamiento Capilar Hidratante Profundo', 'Tratamiento intensivo para restaurar la hidratación y brillo del cabello.', 70000.00, 0, 60, '2025-06-10 19:43:50', '2025-06-10 19:43:50'),
(8, 'prueba', 'prueba', 50000.00, 1, 10, '2025-06-10 22:09:21', NULL),
(9, 'ondulado permanente (Caballero &amp; Dama)', 'nuevo look para tu cabello', 200000.00, 1, 20, '2025-06-11 06:03:20', NULL),
(10, 'prueba ( modificada )', 'prueba', 20000.00, 0, 20, '2025-06-13 00:51:18', '2025-06-13 00:54:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes_atencion`
--

CREATE TABLE `solicitudes_atencion` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo_solicitud` varchar(100) NOT NULL,
  `asunto` varchar(255) NOT NULL,
  `descripcion` text NOT NULL,
  `email_contacto` varchar(255) NOT NULL,
  `estado` varchar(50) NOT NULL DEFAULT 'Abierta',
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `respuesta_admin` text DEFAULT NULL,
  `admin_id_respuesta` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `solicitudes_atencion`
--

INSERT INTO `solicitudes_atencion` (`id`, `usuario_id`, `tipo_solicitud`, `asunto`, `descripcion`, `email_contacto`, `estado`, `fecha_creacion`, `fecha_actualizacion`, `respuesta_admin`, `admin_id_respuesta`) VALUES
(5, 16, 'Consulta', 'prueba', 'prueba', 'cliente@gmail.com', 'Resuelta', '2025-06-12 20:42:56', '2025-06-12 20:54:09', 'que pena, prueba', 14);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(255) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `password` varchar(255) NOT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `fecha_modificacion` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `fotografia` varchar(255) DEFAULT NULL,
  `rol_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `email`, `telefono`, `activo`, `password`, `fecha_registro`, `fecha_modificacion`, `fotografia`, `rol_id`) VALUES
(11, 'empleado', 'prueba', 'empleado@gmail.com', '', 1, '$2y$10$8comomHQc5OkuyslSa2PNuHHaNyxZCXqbpgajXs3bDqS6.oqLHJBC', '2025-06-08 11:37:02', '2025-06-13 01:59:17', 'user_11_684b85f527c9b9.00188293.jpg', 3),
(14, 'Harol Daniel', 'Maldonado Arismendi', 'harolmaldonado14@gmail.com', '3017678950', 1, '$2y$10$yNVqCl2vEWKjc1CF7KcAr.d6oPN72fxTHWl.OITTerXHB55Wr/W3K', '2025-06-08 17:03:13', '2025-06-12 19:38:42', 'user_14_684b2cc2e734a3.64130949.jpg', 1),
(16, 'cliente', 'prueba', 'cliente@gmail.com', '', 0, '$2y$10$Agi3EI/vscWdirW2D3qTVe9qOK03L1OXL5WI6/MjauwQlng.xnCYm', '2025-06-09 14:54:18', '2025-06-13 02:00:41', 'user_16_684b8649e70386.72778038.jpg', 2),
(22, 'Harol Daniel', NULL, 'prueba@gmail.com', NULL, 1, '$2y$10$UvT4D1bsgjGm3Talxxnqbu0..MfGWgh9E.jsPrCRTZAxSDgaHa9GO', '2025-06-12 18:59:54', '2025-06-12 23:59:54', NULL, 2);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_nombre_categoria_unico` (`nombre`);

--
-- Indices de la tabla `citas`
--
ALTER TABLE `citas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `empleado_id` (`empleado_id`),
  ADD KEY `servicio_id` (`servicio_id`);

--
-- Indices de la tabla `empleado_horarios_recurrentes`
--
ALTER TABLE `empleado_horarios_recurrentes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_horario_empleado_dia` (`empleado_id`,`dia_semana`);

--
-- Indices de la tabla `empleado_horario_excepciones`
--
ALTER TABLE `empleado_horario_excepciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_excepcion_empleado_fecha` (`empleado_id`,`fecha`);

--
-- Indices de la tabla `empleado_servicios`
--
ALTER TABLE `empleado_servicios`
  ADD PRIMARY KEY (`empleado_id`,`servicio_id`),
  ADD KEY `idx_empleado_servicios_empleado` (`empleado_id`),
  ADD KEY `idx_empleado_servicios_servicio` (`servicio_id`);

--
-- Indices de la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_factura_unique` (`numero_factura`),
  ADD KEY `fk_factura_cita` (`cita_id`),
  ADD KEY `fk_factura_cliente` (`cliente_id`),
  ADD KEY `fk_factura_empleado` (`empleado_id`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario_id_destino` (`usuario_id_destino`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_categoria_id` (`categoria_id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre_rol` (`nombre_rol`);

--
-- Indices de la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `solicitudes_atencion`
--
ALTER TABLE `solicitudes_atencion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario_id_solicitud` (`usuario_id`),
  ADD KEY `idx_estado_solicitud` (`estado`),
  ADD KEY `fk_solicitud_admin_respuesta` (`admin_id_respuesta`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `email_2` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `citas`
--
ALTER TABLE `citas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `empleado_horarios_recurrentes`
--
ALTER TABLE `empleado_horarios_recurrentes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `empleado_horario_excepciones`
--
ALTER TABLE `empleado_horario_excepciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `facturas`
--
ALTER TABLE `facturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `solicitudes_atencion`
--
ALTER TABLE `solicitudes_atencion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `citas`
--
ALTER TABLE `citas`
  ADD CONSTRAINT `citas_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `citas_ibfk_2` FOREIGN KEY (`empleado_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `citas_ibfk_3` FOREIGN KEY (`servicio_id`) REFERENCES `servicios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `empleado_horarios_recurrentes`
--
ALTER TABLE `empleado_horarios_recurrentes`
  ADD CONSTRAINT `empleado_horarios_recurrentes_ibfk_1` FOREIGN KEY (`empleado_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `empleado_horario_excepciones`
--
ALTER TABLE `empleado_horario_excepciones`
  ADD CONSTRAINT `empleado_horario_excepciones_ibfk_1` FOREIGN KEY (`empleado_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `empleado_servicios`
--
ALTER TABLE `empleado_servicios`
  ADD CONSTRAINT `empleado_servicios_ibfk_1` FOREIGN KEY (`empleado_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `empleado_servicios_ibfk_2` FOREIGN KEY (`servicio_id`) REFERENCES `servicios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD CONSTRAINT `fk_factura_cita` FOREIGN KEY (`cita_id`) REFERENCES `citas` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_factura_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_factura_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `fk_notificacion_usuario` FOREIGN KEY (`usuario_id_destino`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `fk_producto_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `solicitudes_atencion`
--
ALTER TABLE `solicitudes_atencion`
  ADD CONSTRAINT `fk_solicitud_admin_respuesta` FOREIGN KEY (`admin_id_respuesta`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_solicitud_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

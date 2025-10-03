-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 03-10-2025 a las 23:53:13
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
-- Base de datos: `proyecto_seguridad2`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre_categoria` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_prestamo`
--

CREATE TABLE `detalle_prestamo` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_prestamo` int(10) UNSIGNED NOT NULL,
  `id_serie` int(10) UNSIGNED NOT NULL,
  `id_recurso` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado`
--

CREATE TABLE `estado` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre_estado` varchar(50) NOT NULL,
  `descripcion_estado` varchar(140) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `incidente`
--

CREATE TABLE `incidente` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_recurso` int(10) UNSIGNED NOT NULL,
  `id_supervisor` int(10) UNSIGNED NOT NULL,
  `id_incidente_detalle` int(10) UNSIGNED NOT NULL,
  `id_usuario_creacion` int(10) UNSIGNED NOT NULL,
  `id_usuario_modificacion` int(10) UNSIGNED NOT NULL,
  `descripcion` varchar(250) DEFAULT NULL,
  `fecha_incidente` datetime NOT NULL,
  `fecha_creacion` datetime NOT NULL,
  `fecha_modificacion` datetime NOT NULL,
  `fecha_cierre_incidente` datetime NOT NULL,
  `resolucion` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `incidente_detalle`
--

CREATE TABLE `incidente_detalle` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_incidente` int(10) UNSIGNED NOT NULL,
  `id_serie` int(10) UNSIGNED NOT NULL,
  `descripcion` varchar(140) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prestamo`
--

CREATE TABLE `prestamo` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `id_usuario_creacion` int(10) UNSIGNED NOT NULL,
  `id_usuario_modificacion` int(10) UNSIGNED NOT NULL,
  `fecha_prestamo` datetime NOT NULL,
  `fecha_devolucion` datetime DEFAULT NULL,
  `estado` int(11) NOT NULL,
  `fecha_creacion` datetime NOT NULL,
  `fecha_modificacion` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recurso`
--

CREATE TABLE `recurso` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_categoria` int(10) UNSIGNED NOT NULL,
  `id_estado` int(10) UNSIGNED NOT NULL,
  `id_incidente_detalle` int(10) UNSIGNED NOT NULL,
  `id_usuario_creacion` int(10) UNSIGNED NOT NULL,
  `id_usuario_modificacion` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(60) NOT NULL,
  `descripcion` varchar(250) DEFAULT NULL,
  `costo_unitario` float(10,2) NOT NULL,
  `fecha_creacion` datetime NOT NULL,
  `fecha_modificacion` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre_rol` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`id`, `nombre_rol`) VALUES
(1, 'Administrador'),
(2, 'Supervisor'),
(3, 'Trabajador');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `serie_recurso`
--

CREATE TABLE `serie_recurso` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_recurso` int(10) UNSIGNED NOT NULL,
  `id_incidente_detalle` int(10) UNSIGNED NOT NULL,
  `nro_serie` varchar(17) NOT NULL,
  `talle` int(10) UNSIGNED DEFAULT NULL,
  `fecha_adquisicion` datetime NOT NULL,
  `fecha_vencimiento` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_rol` int(10) UNSIGNED NOT NULL COMMENT 'rol del usuario, clave foranea',
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `usuario_creacion` int(10) UNSIGNED DEFAULT NULL,
  `usuario_modificacion` int(10) UNSIGNED DEFAULT NULL,
  `ultimo_acceso` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id`, `id_rol`, `name`, `email`, `password`, `created_at`, `updated_at`, `usuario_creacion`, `usuario_modificacion`, `ultimo_acceso`) VALUES
(5, 1, 'Admin Restaurado', 'admin@empresa.com', '$2y$12$UXwLLgfJwN7DU0ZICwtOJOM/LGRQgaxL4GB05.cdexpN/1f1II/MK', '2025-10-03 18:08:23', '2025-10-03 21:43:14', NULL, NULL, '2025-10-03 21:43:14'),
(6, 2, 'supervisor14', 'sup@empresa.com', '$2y$12$RzZoB461wF/csEEhwnXvke6Tcq1PGGrsIVN5XXEibSLPPWlreZVDK', '2025-10-03 21:42:12', '2025-10-03 21:42:12', 5, 5, '2025-10-03 21:42:12'),
(7, 3, 'trabajador', 'traba@gmail.com', '$2y$12$TFhscjYuiCjO6VgqA8iRe.CY0A2/U6ZQSjV0TVOk/PA984zBtDRLi', '2025-10-03 21:44:00', '2025-10-03 21:44:20', 5, 5, '2025-10-03 21:44:20');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `detalle_prestamo`
--
ALTER TABLE `detalle_prestamo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_prestamo` (`id_prestamo`),
  ADD KEY `id_serie` (`id_serie`),
  ADD KEY `id_recurso` (`id_recurso`);

--
-- Indices de la tabla `estado`
--
ALTER TABLE `estado`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `incidente`
--
ALTER TABLE `incidente`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_recurso` (`id_recurso`),
  ADD KEY `id_supervisor` (`id_supervisor`),
  ADD KEY `id_incidente_detalle` (`id_incidente_detalle`),
  ADD KEY `id_usuario_creacion` (`id_usuario_creacion`),
  ADD KEY `id_usuario_modificacion` (`id_usuario_modificacion`);

--
-- Indices de la tabla `incidente_detalle`
--
ALTER TABLE `incidente_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_incidente` (`id_incidente`),
  ADD KEY `id_serie` (`id_serie`);

--
-- Indices de la tabla `prestamo`
--
ALTER TABLE `prestamo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_usuario_creacion` (`id_usuario_creacion`),
  ADD KEY `id_usuario_modificacion` (`id_usuario_modificacion`);

--
-- Indices de la tabla `recurso`
--
ALTER TABLE `recurso`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_categoria` (`id_categoria`),
  ADD KEY `id_estado` (`id_estado`),
  ADD KEY `id_incidente_detalle` (`id_incidente_detalle`),
  ADD KEY `id_usuario_creacion` (`id_usuario_creacion`),
  ADD KEY `id_usuario_modificacion` (`id_usuario_modificacion`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `serie_recurso`
--
ALTER TABLE `serie_recurso`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_recurso` (`id_recurso`),
  ADD KEY `id_incidente_detalle` (`id_incidente_detalle`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_rol` (`id_rol`),
  ADD KEY `idx_usuario_creacion` (`usuario_creacion`),
  ADD KEY `idx_usuario_modificacion` (`usuario_modificacion`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalle_prestamo`
--
ALTER TABLE `detalle_prestamo`
  ADD CONSTRAINT `detalle_prestamo_ibfk_1` FOREIGN KEY (`id_prestamo`) REFERENCES `prestamo` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `detalle_prestamo_ibfk_2` FOREIGN KEY (`id_serie`) REFERENCES `serie_recurso` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `detalle_prestamo_ibfk_3` FOREIGN KEY (`id_recurso`) REFERENCES `recurso` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `incidente`
--
ALTER TABLE `incidente`
  ADD CONSTRAINT `incidente_ibfk_1` FOREIGN KEY (`id_recurso`) REFERENCES `recurso` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `incidente_ibfk_2` FOREIGN KEY (`id_incidente_detalle`) REFERENCES `incidente_detalle` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `incidente_ibfk_3` FOREIGN KEY (`id_usuario_creacion`) REFERENCES `usuario` (`usuario_creacion`) ON UPDATE CASCADE,
  ADD CONSTRAINT `incidente_ibfk_4` FOREIGN KEY (`id_usuario_modificacion`) REFERENCES `usuario` (`usuario_modificacion`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `incidente_detalle`
--
ALTER TABLE `incidente_detalle`
  ADD CONSTRAINT `incidente_detalle_ibfk_1` FOREIGN KEY (`id_incidente`) REFERENCES `incidente` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `prestamo`
--
ALTER TABLE `prestamo`
  ADD CONSTRAINT `prestamo_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `prestamo_ibfk_2` FOREIGN KEY (`id_usuario_creacion`) REFERENCES `usuario` (`usuario_creacion`) ON UPDATE CASCADE,
  ADD CONSTRAINT `prestamo_ibfk_3` FOREIGN KEY (`id_usuario_modificacion`) REFERENCES `usuario` (`usuario_modificacion`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `recurso`
--
ALTER TABLE `recurso`
  ADD CONSTRAINT `recurso_ibfk_1` FOREIGN KEY (`id_estado`) REFERENCES `estado` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `recurso_ibfk_2` FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `recurso_ibfk_3` FOREIGN KEY (`id_incidente_detalle`) REFERENCES `incidente_detalle` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `recurso_ibfk_4` FOREIGN KEY (`id_usuario_creacion`) REFERENCES `usuario` (`usuario_creacion`) ON UPDATE CASCADE,
  ADD CONSTRAINT `recurso_ibfk_5` FOREIGN KEY (`id_usuario_modificacion`) REFERENCES `usuario` (`usuario_modificacion`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `serie_recurso`
--
ALTER TABLE `serie_recurso`
  ADD CONSTRAINT `serie_recurso_ibfk_1` FOREIGN KEY (`id_recurso`) REFERENCES `recurso` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `serie_recurso_ibfk_2` FOREIGN KEY (`id_incidente_detalle`) REFERENCES `incidente_detalle` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `fk_usuario_creacion` FOREIGN KEY (`usuario_creacion`) REFERENCES `usuario` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_usuario_modificacion` FOREIGN KEY (`usuario_modificacion`) REFERENCES `usuario` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

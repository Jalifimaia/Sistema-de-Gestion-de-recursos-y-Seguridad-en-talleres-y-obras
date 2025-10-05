-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 05-10-2025 a las 07:34:12
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
-- Estructura de tabla para la tabla `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre_categoria` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`id`, `nombre_categoria`) VALUES
(1, 'EPP'),
(2, 'Herramienta');

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

--
-- Volcado de datos para la tabla `estado`
--

INSERT INTO `estado` (`id`, `nombre_estado`, `descripcion_estado`) VALUES
(1, 'Disponible', ''),
(2, 'Baja', ''),
(3, 'Prestado', ''),
(4, 'Devuelto', ''),
(5, 'Dañado', ''),
(6, 'En Reparación', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado_usuario`
--

CREATE TABLE `estado_usuario` (
  `id` int(10) NOT NULL,
  `nombre` varchar(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estado_usuario`
--

INSERT INTO `estado_usuario` (`id`, `nombre`) VALUES
(1, 'Alta'),
(2, 'Baja'),
(3, 'stand by');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Estructura de tabla para la tabla `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_10_02_161535_add_campos_personalizados_to_users_table', 1),
(5, '2025_10_02_212636_add_ultimo_acceso_to_users_table', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `id` int(11) UNSIGNED NOT NULL,
  `id_estado` int(10) UNSIGNED NOT NULL,
  `id_incidente_detalle` int(10) UNSIGNED DEFAULT NULL,
  `id_usuario_creacion` int(10) UNSIGNED NOT NULL,
  `id_usuario_modificacion` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(60) NOT NULL,
  `descripcion` varchar(250) DEFAULT NULL,
  `costo_unitario` float(10,2) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `id_subcategoria` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

--
-- Volcado de datos para la tabla `recurso`
--

INSERT INTO `recurso` (`id`, `id_estado`, `id_incidente_detalle`, `id_usuario_creacion`, `id_usuario_modificacion`, `nombre`, `descripcion`, `costo_unitario`, `fecha_creacion`, `fecha_modificacion`, `created_at`, `updated_at`, `id_subcategoria`) VALUES
(3, 1, NULL, 5, 5, 'Chaleco Marca Pirulo', 'vhhghjgjhghjghjgjgj', 56000.00, '2025-10-05 02:09:09', '2025-10-05 02:09:09', '2025-10-05 05:09:09', '2025-10-05 05:09:09', 2),
(4, 1, NULL, 5, 5, 'Chaleco Marca Pepito', 'qqqqqqqqqq', 52000.00, '2025-10-05 02:09:53', '2025-10-05 02:09:53', '2025-10-05 05:09:53', '2025-10-05 05:09:53', 2),
(5, 1, NULL, 5, 5, 'Casco  123', 'Casco Amarillo', 20000.00, '2025-10-05 02:30:11', '2025-10-05 02:30:11', '2025-10-05 05:30:11', '2025-10-05 05:30:11', 4);

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
  `id` int(11) UNSIGNED NOT NULL,
  `id_recurso` int(10) UNSIGNED NOT NULL,
  `id_incidente_detalle` int(10) UNSIGNED DEFAULT NULL,
  `nro_serie` varchar(17) NOT NULL,
  `talle` int(10) UNSIGNED DEFAULT NULL,
  `fecha_adquisicion` datetime NOT NULL,
  `fecha_vencimiento` datetime NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `stock`
--

CREATE TABLE `stock` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_recurso` int(10) UNSIGNED NOT NULL,
  `id_serie_recurso` int(10) UNSIGNED DEFAULT NULL,
  `id_estado_recurso` int(10) UNSIGNED NOT NULL,
  `id_usuario` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subcategoria`
--

CREATE TABLE `subcategoria` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `categoria_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `subcategoria`
--

INSERT INTO `subcategoria` (`id`, `nombre`, `categoria_id`) VALUES
(1, 'Martillo', 2),
(2, 'Chaleco', 1),
(3, 'Test', 1),
(4, 'Casco', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `id_rol` bigint(20) UNSIGNED DEFAULT NULL,
  `usuario_creacion` bigint(20) UNSIGNED DEFAULT NULL,
  `usuario_modificacion` bigint(20) UNSIGNED DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `ultimo_acceso` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `ultimo_acceso` datetime DEFAULT NULL,
  `id_estado` int(10) DEFAULT NULL,
  `fecha_nacimiento` datetime DEFAULT NULL,
  `dni` varchar(15) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `nro_legajo` int(11) DEFAULT NULL,
  `auth_key` varchar(255) DEFAULT NULL,
  `access_token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id`, `id_rol`, `name`, `email`, `password`, `created_at`, `updated_at`, `usuario_creacion`, `usuario_modificacion`, `ultimo_acceso`, `id_estado`, `fecha_nacimiento`, `dni`, `telefono`, `nro_legajo`, `auth_key`, `access_token`) VALUES
(5, 1, 'Admin Restaurado', 'admin@empresa.com', '$2y$12$UXwLLgfJwN7DU0ZICwtOJOM/LGRQgaxL4GB05.cdexpN/1f1II/MK', '2025-10-03 18:08:23', '2025-10-05 00:30:50', NULL, NULL, '2025-10-05 00:30:50', 1, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 2, 'supervisor14', 'sup@empresa.com', '$2y$12$RzZoB461wF/csEEhwnXvke6Tcq1PGGrsIVN5XXEibSLPPWlreZVDK', '2025-10-03 21:42:12', '2025-10-03 21:42:12', 5, 5, '2025-10-03 21:42:12', 1, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 3, 'trabajador', 'traba@gmail.com', '$2y$12$TFhscjYuiCjO6VgqA8iRe.CY0A2/U6ZQSjV0TVOk/PA984zBtDRLi', '2025-10-03 21:44:00', '2025-10-03 21:44:20', 5, 5, '2025-10-03 21:44:20', 1, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 3, 'David', 'david@gmail.com', '$2y$12$l.ikdT365X7RBrvj2Dn39ueD.yu6xcISDf0.1avy2Uk5FgTFVge4G', '2025-10-04 01:49:47', '2025-10-04 01:49:47', 5, 5, '2025-10-04 01:49:47', 3, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 3, 'Tuti', 'hola@gmail.com', '$2y$12$gsTfJ1SvZv23pMdPvMoPyevF1mU06rGVAXaXaH/mFLxGP7Jj3ltbO', '2025-10-04 02:04:56', '2025-10-04 02:04:56', 5, 5, '2025-10-04 02:04:56', 3, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 3, 'userprueba', 'user@gmail.com', '$2y$12$StRvDRhkkWWjZFSQgzmjcOueFq5mh2QXU5eV1RixsfqnScflf3vV.', '2025-10-04 02:07:02', '2025-10-04 02:07:02', 5, 5, '2025-10-04 02:07:02', 3, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 2, 'user2', 'user2@gmail.com', '$2y$12$SRAh1tdXbYlz8o64bcx/muoTzwWpW9fbaXBpDr4N7InI8fOamUHBi', '2025-10-04 02:08:49', '2025-10-04 02:08:49', 5, 5, '2025-10-04 02:08:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 3, 'aaaa', 'eee@gmail.com', '$2y$12$6PXfvfB4iNUZpnE.DV.gd.tgzhEEjO4zUjSx0KM8zpf6o.fYQ0Yem', '2025-10-04 02:09:24', '2025-10-04 02:09:24', 5, 5, '2025-10-04 02:09:24', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 1, 'miki', 'miki@gmail.com', '$2y$12$34yPOTEHJiOkR/XO5H4q1eq1t9LHam0HyNt8T9kH65HuwrrwdF0Yu', '2025-10-04 02:13:01', '2025-10-04 02:13:01', 5, 5, '2025-10-04 02:13:01', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14, 3, 'mimi', 'mimi@gmail.com', '$2y$12$AqMIKF5KyQt0EuMwAVYKtOfnog5xPuZi9pz1i.duy8X4REbhmzfza', '2025-10-04 02:15:12', '2025-10-04 02:15:12', 5, 5, '2025-10-04 02:15:12', 1, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_recurso`
--

CREATE TABLE `usuario_recurso` (
  `id` int(10) NOT NULL,
  `id_usuario` int(10) NOT NULL,
  `id_recurso` int(10) NOT NULL,
  `fecha_asignacion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indices de la tabla `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

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
-- Indices de la tabla `estado_usuario`
--
ALTER TABLE `estado_usuario`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

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
-- Indices de la tabla `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indices de la tabla `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

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
  ADD KEY `id_estado` (`id_estado`),
  ADD KEY `id_incidente_detalle` (`id_incidente_detalle`),
  ADD KEY `id_usuario_creacion` (`id_usuario_creacion`),
  ADD KEY `id_usuario_modificacion` (`id_usuario_modificacion`),
  ADD KEY `fk_subcategoria` (`id_subcategoria`);

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
-- Indices de la tabla `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indices de la tabla `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_recurso` (`id_recurso`),
  ADD KEY `idx_serie_recurso` (`id_serie_recurso`),
  ADD KEY `idx_estado_recurso` (`id_estado_recurso`);

--
-- Indices de la tabla `subcategoria`
--
ALTER TABLE `subcategoria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `index_subcategoria` (`categoria_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dni_unique` (`dni`),
  ADD UNIQUE KEY `legajo_unique` (`nro_legajo`),
  ADD KEY `id_rol` (`id_rol`),
  ADD KEY `idx_usuario_creacion` (`usuario_creacion`),
  ADD KEY `idx_usuario_modificacion` (`usuario_modificacion`),
  ADD KEY `id_estado` (`id_estado`);

--
-- Indices de la tabla `usuario_recurso`
--
ALTER TABLE `usuario_recurso`
  ADD PRIMARY KEY (`id`),
  ADD KEY `index_usuario` (`id_usuario`,`id_recurso`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `estado_usuario`
--
ALTER TABLE `estado_usuario`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `recurso`
--
ALTER TABLE `recurso`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `serie_recurso`
--
ALTER TABLE `serie_recurso`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `stock`
--
ALTER TABLE `stock`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `subcategoria`
--
ALTER TABLE `subcategoria`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `usuario_recurso`
--
ALTER TABLE `usuario_recurso`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

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
  ADD CONSTRAINT `fk_subcategoria` FOREIGN KEY (`id_subcategoria`) REFERENCES `subcategoria` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `recurso_ibfk_1` FOREIGN KEY (`id_estado`) REFERENCES `estado` (`id`) ON UPDATE CASCADE,
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
-- Filtros para la tabla `subcategoria`
--
ALTER TABLE `subcategoria`
  ADD CONSTRAINT `fk_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categoria` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `fk_estado` FOREIGN KEY (`id_estado`) REFERENCES `estado_usuario` (`id`) ON DELETE NO ACTION,
  ADD CONSTRAINT `fk_usuario_creacion` FOREIGN KEY (`usuario_creacion`) REFERENCES `usuario` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_usuario_modificacion` FOREIGN KEY (`usuario_modificacion`) REFERENCES `usuario` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

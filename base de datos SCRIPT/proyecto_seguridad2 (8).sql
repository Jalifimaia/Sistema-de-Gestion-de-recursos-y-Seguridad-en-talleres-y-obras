-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 16-10-2025 a las 00:39:37
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
  `id_recurso` int(10) UNSIGNED NOT NULL,
  `id_estado_prestamo` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

--
-- Volcado de datos para la tabla `detalle_prestamo`
--

INSERT INTO `detalle_prestamo` (`id`, `id_prestamo`, `id_serie`, `id_recurso`, `id_estado_prestamo`, `created_at`, `updated_at`) VALUES
(1, 2, 4, 4, 2, NULL, NULL),
(3, 4, 4, 4, 2, NULL, NULL),
(4, 6, 6, 6, 5, NULL, NULL),
(10, 12, 10, 8, 5, NULL, NULL),
(11, 16, 20, 10, 2, '2025-10-15 02:55:45', '2025-10-15 02:55:45'),
(12, 17, 25, 10, 2, '2025-10-15 02:57:21', '2025-10-15 02:57:21'),
(13, 17, 19, 10, 2, '2025-10-15 02:57:21', '2025-10-15 02:57:21');

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
-- Estructura de tabla para la tabla `estado_incidente`
--

CREATE TABLE `estado_incidente` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre_estado` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estado_incidente`
--

INSERT INTO `estado_incidente` (`id`, `nombre_estado`) VALUES
(2, 'En revisión'),
(6, 'Escalado'),
(4, 'Falso / descartado'),
(1, 'Reportado'),
(5, 'Resuelto'),
(3, 'Validado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado_prestamo`
--

CREATE TABLE `estado_prestamo` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estado_prestamo`
--

INSERT INTO `estado_prestamo` (`id`, `nombre`) VALUES
(1, 'Pendiente'),
(2, 'Activo'),
(3, 'Devuelto'),
(4, 'Vencido'),
(5, 'Cancelado'),
(6, 'En revisión'),
(7, 'Rechazado');

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
  `id_recurso` int(10) UNSIGNED DEFAULT NULL,
  `id_serie_recurso` int(10) UNSIGNED DEFAULT NULL,
  `id_supervisor` int(10) UNSIGNED NOT NULL,
  `id_trabajador` int(10) UNSIGNED DEFAULT NULL,
  `id_incidente_detalle` int(10) UNSIGNED DEFAULT NULL,
  `id_usuario_creacion` int(10) UNSIGNED DEFAULT NULL,
  `id_usuario_modificacion` int(10) UNSIGNED DEFAULT NULL,
  `descripcion` varchar(250) DEFAULT NULL,
  `fecha_incidente` datetime NOT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_modificacion` datetime DEFAULT current_timestamp(),
  `fecha_cierre_incidente` datetime DEFAULT NULL,
  `resolucion` varchar(250) DEFAULT NULL,
  `id_estado_incidente` int(10) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

--
-- Volcado de datos para la tabla `incidente`
--

INSERT INTO `incidente` (`id`, `id_recurso`, `id_serie_recurso`, `id_supervisor`, `id_trabajador`, `id_incidente_detalle`, `id_usuario_creacion`, `id_usuario_modificacion`, `descripcion`, `fecha_incidente`, `fecha_creacion`, `fecha_modificacion`, `fecha_cierre_incidente`, `resolucion`, `id_estado_incidente`) VALUES
(29, NULL, NULL, 5, 8, NULL, NULL, NULL, '.', '2025-10-20 01:33:00', '2025-10-14 01:34:55', '2025-10-14 01:34:55', NULL, NULL, 2),
(30, NULL, NULL, 5, 8, NULL, NULL, NULL, 'Se cayo', '2025-10-23 01:35:00', '2025-10-14 01:35:42', '2025-10-14 01:35:42', NULL, 'No hay', 1),
(31, NULL, NULL, 5, 8, NULL, NULL, NULL, 'Se rompio el mango', '2025-10-28 13:51:00', '2025-10-14 13:51:50', '2025-10-14 13:51:50', NULL, '-', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `incidente_detalle`
--

CREATE TABLE `incidente_detalle` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_incidente` int(10) UNSIGNED DEFAULT NULL,
  `id_serie` int(10) UNSIGNED NOT NULL,
  `descripcion` varchar(140) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

--
-- Volcado de datos para la tabla `incidente_detalle`
--

INSERT INTO `incidente_detalle` (`id`, `id_incidente`, `id_serie`, `descripcion`) VALUES
(0, 1, 5, 'Corte de cable por sobrecalentamiento');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `incidente_recurso`
--

CREATE TABLE `incidente_recurso` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_incidente` int(10) UNSIGNED NOT NULL,
  `id_recurso` int(10) UNSIGNED NOT NULL,
  `id_serie_recurso` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `incidente_recurso`
--

INSERT INTO `incidente_recurso` (`id`, `id_incidente`, `id_recurso`, `id_serie_recurso`, `created_at`, `updated_at`) VALUES
(1, 29, 8, 10, '2025-10-14 04:34:55', '2025-10-14 04:34:55'),
(2, 29, 4, 4, '2025-10-14 04:34:55', '2025-10-14 04:34:55'),
(6, 30, 4, 4, '2025-10-14 04:42:40', '2025-10-14 04:42:40'),
(7, 30, 9, 14, '2025-10-14 04:42:40', '2025-10-14 04:42:40'),
(8, 30, 8, 10, '2025-10-14 04:42:40', '2025-10-14 04:42:40'),
(11, 31, 4, 4, '2025-10-14 16:55:19', '2025-10-14 16:55:19'),
(12, 31, 8, 11, '2025-10-14 16:55:19', '2025-10-14 16:55:19');

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
(5, '2025_10_02_212636_add_ultimo_acceso_to_users_table', 1),
(6, '2025_10_03_020124_remove_fecha_columns_from_recurso_table', 2);

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

--
-- Volcado de datos para la tabla `prestamo`
--

INSERT INTO `prestamo` (`id`, `id_usuario`, `id_usuario_creacion`, `id_usuario_modificacion`, `fecha_prestamo`, `fecha_devolucion`, `estado`, `fecha_creacion`, `fecha_modificacion`) VALUES
(1, 7, 6, 6, '2025-10-06 23:51:37', NULL, 1, '2025-10-06 23:51:37', '2025-10-06 23:51:37'),
(2, 7, 6, 6, '2025-10-07 00:12:55', NULL, 1, '2025-10-07 00:12:55', '2025-10-07 00:12:55'),
(3, 7, 6, 6, '2025-10-07 16:04:18', NULL, 1, '2025-10-07 16:04:18', '2025-10-07 16:04:18'),
(4, 7, 6, 6, '2025-10-07 16:20:12', NULL, 1, '2025-10-07 16:20:12', '2025-10-07 16:20:12'),
(6, 5, 5, 5, '2025-10-03 00:00:00', '2025-10-15 00:00:00', 4, '2025-10-08 02:42:26', '2025-10-08 02:42:26'),
(12, 5, 5, 5, '2025-10-08 00:00:00', '2025-10-16 00:00:00', 3, '2025-10-08 16:14:45', '2025-10-08 16:14:45'),
(16, 5, 5, 5, '2025-10-16 00:00:00', '2025-10-22 00:00:00', 2, '2025-10-15 02:55:45', '2025-10-15 02:55:45'),
(17, 5, 5, 5, '2025-10-22 00:00:00', '2025-10-28 00:00:00', 2, '2025-10-15 02:57:21', '2025-10-15 02:57:21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recurso`
--

CREATE TABLE `recurso` (
  `id` int(11) UNSIGNED NOT NULL,
  `id_incidente_detalle` int(10) UNSIGNED DEFAULT NULL,
  `id_usuario_creacion` int(10) UNSIGNED DEFAULT NULL,
  `id_usuario_modificacion` int(10) UNSIGNED DEFAULT NULL,
  `nombre` varchar(60) NOT NULL,
  `descripcion` varchar(250) DEFAULT NULL,
  `costo_unitario` float(10,2) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `id_subcategoria` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

--
-- Volcado de datos para la tabla `recurso`
--

INSERT INTO `recurso` (`id`, `id_incidente_detalle`, `id_usuario_creacion`, `id_usuario_modificacion`, `nombre`, `descripcion`, `costo_unitario`, `created_at`, `updated_at`, `id_subcategoria`) VALUES
(4, NULL, 5, 5, 'Chaleco Marca Pepito', 'Chaleco naranja', 52000.00, '2025-10-05 05:09:53', '2025-10-05 05:09:53', 2),
(5, NULL, 5, 5, 'Casco  123', 'Casco Amarillo', 20000.00, '2025-10-05 05:30:11', '2025-10-05 05:30:11', 4),
(6, NULL, 5, 5, 'Taladro XP', 'Taladro rojo', 20000.00, '2025-10-05 17:59:54', '2025-10-05 17:59:54', 6),
(7, NULL, 5, 5, 'Taladro XP', 'Acero', 20000.00, '2025-10-08 03:06:55', '2025-10-08 03:06:55', 6),
(8, NULL, 5, 5, 'Stanley', 'Azul, acero', 30000.00, '2025-10-08 03:08:01', '2025-10-08 03:08:01', 1),
(9, NULL, 5, 5, 'Casco de prueba', 'Naranja', 10000.00, '2025-10-08 21:34:06', '2025-10-08 21:34:06', 4),
(10, NULL, 5, 5, 'Termonimayc', '.', 2000.00, '2025-10-08 22:12:08', '2025-10-08 22:12:08', 6);

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
  `fecha_vencimiento` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `id_estado` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

--
-- Volcado de datos para la tabla `serie_recurso`
--

INSERT INTO `serie_recurso` (`id`, `id_recurso`, `id_incidente_detalle`, `nro_serie`, `talle`, `fecha_adquisicion`, `fecha_vencimiento`, `created_at`, `updated_at`, `id_estado`) VALUES
(4, 4, NULL, '78YT', 40, '2025-10-05 00:00:00', '2025-10-31 00:00:00', '2025-10-05 15:49:01', '2025-10-15 08:06:44', 4),
(5, 6, NULL, 'XP-001', NULL, '2025-10-05 00:00:00', '2025-10-30 00:00:00', '2025-10-05 18:05:20', '2025-10-05 18:05:20', 3),
(6, 6, NULL, 'XP-002', NULL, '2025-10-05 00:00:00', '2025-10-30 00:00:00', '2025-10-05 18:05:20', '2025-10-15 08:02:09', 4),
(7, 6, NULL, 'XP-003', NULL, '2025-10-05 00:00:00', '2025-10-30 00:00:00', '2025-10-05 18:05:20', '2025-10-05 18:05:20', 3),
(8, 6, NULL, 'XP-004', NULL, '2025-10-05 00:00:00', '2025-10-30 00:00:00', '2025-10-05 18:05:20', '2025-10-05 18:05:20', 3),
(9, 6, NULL, 'XP-005', NULL, '2025-10-05 00:00:00', '2025-10-30 00:00:00', '2025-10-05 18:05:20', '2025-10-05 18:05:20', 3),
(10, 8, NULL, 'GT001', NULL, '2025-10-11 00:00:00', '2025-10-24 00:00:00', '2025-10-08 03:08:52', '2025-10-08 03:08:52', 3),
(11, 8, NULL, 'GT002', NULL, '2025-10-11 00:00:00', '2025-10-24 00:00:00', '2025-10-08 03:08:52', '2025-10-08 03:08:52', 1),
(12, 8, NULL, 'GT003', NULL, '2025-10-11 00:00:00', '2025-10-24 00:00:00', '2025-10-08 03:08:52', '2025-10-08 03:08:52', 1),
(13, 9, NULL, 'GTR001', NULL, '2025-10-22 00:00:00', '2025-10-28 00:00:00', '2025-10-08 21:34:46', '2025-10-08 21:34:46', 1),
(14, 9, NULL, 'GTR002', NULL, '2025-10-22 00:00:00', '2025-10-28 00:00:00', '2025-10-08 21:34:46', '2025-10-08 21:34:46', 1),
(15, 9, NULL, 'GTR003', NULL, '2025-10-22 00:00:00', '2025-10-28 00:00:00', '2025-10-08 21:34:46', '2025-10-08 21:34:46', 1),
(16, 9, NULL, 'GTR004', NULL, '2025-10-22 00:00:00', '2025-10-28 00:00:00', '2025-10-08 21:34:46', '2025-10-08 21:34:46', 1),
(17, 9, NULL, 'GTR005', NULL, '2025-10-22 00:00:00', '2025-10-28 00:00:00', '2025-10-08 21:34:46', '2025-10-08 21:34:46', 1),
(18, 10, NULL, 'GTRT001', NULL, '2025-10-16 00:00:00', '2025-10-21 00:00:00', '2025-10-08 22:12:41', '2025-10-08 22:12:41', 1),
(19, 10, NULL, 'GTRT002', NULL, '2025-10-16 00:00:00', '2025-10-21 00:00:00', '2025-10-08 22:12:41', '2025-10-15 02:57:21', 3),
(20, 10, NULL, 'GTRT003', NULL, '2025-10-16 00:00:00', '2025-10-21 00:00:00', '2025-10-08 22:12:41', '2025-10-15 02:55:45', 3),
(21, 10, NULL, 'GTRT004', NULL, '2025-10-16 00:00:00', '2025-10-21 00:00:00', '2025-10-08 22:12:41', '2025-10-08 22:12:41', 1),
(22, 10, NULL, 'GTRT005', NULL, '2025-10-16 00:00:00', '2025-10-21 00:00:00', '2025-10-08 22:12:41', '2025-10-08 22:12:41', 1),
(23, 10, NULL, 'GTRT006', NULL, '2025-10-16 00:00:00', '2025-10-21 00:00:00', '2025-10-08 22:12:41', '2025-10-08 22:12:41', 1),
(24, 10, NULL, 'GTRT007', NULL, '2025-10-16 00:00:00', '2025-10-21 00:00:00', '2025-10-08 22:12:41', '2025-10-08 22:12:41', 1),
(25, 10, NULL, 'GTRT008', NULL, '2025-10-16 00:00:00', '2025-10-21 00:00:00', '2025-10-08 22:12:41', '2025-10-08 22:12:41', 1);

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

--
-- Volcado de datos para la tabla `stock`
--

INSERT INTO `stock` (`id`, `id_recurso`, `id_serie_recurso`, `id_estado_recurso`, `id_usuario`) VALUES
(1, 10, 20, 3, 5),
(2, 10, 25, 3, 5),
(3, 10, 19, 3, 5);

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
(4, 'Casco', 1),
(5, 'Arnes', 1),
(6, 'Taladro', 2);

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
(5, 1, 'Admin Restaurado', 'admin@empresa.com', '$2y$12$UXwLLgfJwN7DU0ZICwtOJOM/LGRQgaxL4GB05.cdexpN/1f1II/MK', '2025-10-03 18:08:23', '2025-10-15 02:18:22', NULL, NULL, '2025-10-15 02:18:22', 1, NULL, '8', NULL, NULL, NULL, NULL),
(6, 2, 'supervisor14', 'sup@empresa.com', '$2y$12$RzZoB461wF/csEEhwnXvke6Tcq1PGGrsIVN5XXEibSLPPWlreZVDK', '2025-10-03 21:42:12', '2025-10-13 18:29:09', 5, 5, '2025-10-11 15:18:10', 2, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 3, 'trabajador', 'trabajador@gmail.com', '$2y$12$TFhscjYuiCjO6VgqA8iRe.CY0A2/U6ZQSjV0TVOk/PA984zBtDRLi', '2025-10-03 21:44:00', '2025-10-13 18:19:12', 5, 5, '2025-10-03 21:44:20', 1, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 3, 'David', 'david@gmail.com', '$2y$12$l.ikdT365X7RBrvj2Dn39ueD.yu6xcISDf0.1avy2Uk5FgTFVge4G', '2025-10-04 01:49:47', '2025-10-13 21:14:17', 5, 5, '2025-10-08 21:49:12', 1, NULL, '1', NULL, NULL, NULL, NULL),
(9, 3, 'Tuti', 'hola@gmail.com', '$2y$12$gsTfJ1SvZv23pMdPvMoPyevF1mU06rGVAXaXaH/mFLxGP7Jj3ltbO', '2025-10-04 02:04:56', '2025-10-12 23:06:57', 5, 5, '2025-10-04 02:04:56', 2, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 3, 'userprueba', 'user@gmail.com', '$2y$12$StRvDRhkkWWjZFSQgzmjcOueFq5mh2QXU5eV1RixsfqnScflf3vV.', '2025-10-04 02:07:02', '2025-10-13 18:43:35', 5, 5, '2025-10-04 02:07:02', 2, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 2, 'user2', 'user2@gmail.com', '$2y$12$SRAh1tdXbYlz8o64bcx/muoTzwWpW9fbaXBpDr4N7InI8fOamUHBi', '2025-10-04 02:08:49', '2025-10-04 02:08:49', 5, 5, '2025-10-04 02:08:49', 2, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 2, 'aaaa3', 'eee@gmail.com', '$2y$12$6PXfvfB4iNUZpnE.DV.gd.tgzhEEjO4zUjSx0KM8zpf6o.fYQ0Yem', '2025-10-04 02:09:24', '2025-10-13 18:31:44', 5, 5, '2025-10-04 02:09:24', 2, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 1, 'miki', 'miki@gmail.com', '$2y$12$34yPOTEHJiOkR/XO5H4q1eq1t9LHam0HyNt8T9kH65HuwrrwdF0Yu', '2025-10-04 02:13:01', '2025-10-13 18:55:55', 5, 5, '2025-10-04 02:13:01', 3, NULL, NULL, NULL, NULL, NULL, NULL),
(14, 3, 'mimi', 'mimi@gmail.com', '$2y$12$AqMIKF5KyQt0EuMwAVYKtOfnog5xPuZi9pz1i.duy8X4REbhmzfza', '2025-10-04 02:15:12', '2025-10-13 21:14:32', 5, 5, '2025-10-04 02:15:12', 1, NULL, '3', NULL, NULL, NULL, NULL),
(19, 1, 'test13-10', 'test13@empresa.com', '$2y$12$yOiNgGpcZYCJ.gRcjqxKeuKQbncs.ydJIh2EXBlSNHO0AbogJvmr.', '2025-10-13 19:49:08', '2025-10-13 22:56:25', 5, 5, '2025-10-13 19:49:08', 2, NULL, '55555', NULL, NULL, NULL, NULL),
(20, 2, 'test15', 'test15@empresa.com', '$2y$12$eljJDNiHnzO5ubrXtoZOsekSgNUICJC00GrK8tyEmUE0S2DxD93k2', '2025-10-13 19:50:06', '2025-10-13 22:42:54', 5, 5, '2025-10-13 19:50:06', 2, NULL, '345345', NULL, NULL, NULL, NULL),
(21, 2, 'gestion8', 'gestion66@empresa.com', '$2y$12$doXQ8vbMZSPWEXgsz8G/G.AkfQ/Xpq.oof09wyHZdjvzQLaGTy.0i', '2025-10-13 20:10:36', '2025-10-13 22:40:32', 5, 5, '2025-10-13 20:10:36', 2, NULL, '3273', NULL, NULL, NULL, NULL),
(22, 1, 'Jaun', 'r@gmail.com', '$2y$12$Bc4NsElMD8hJUx0eEbgZi.oa.EwU2vEuFzl7CixDZR3tgQw4k15eO', '2025-10-14 17:17:03', '2025-10-14 17:17:03', 5, 5, '2025-10-14 17:17:03', 3, NULL, '43', NULL, NULL, NULL, NULL),
(23, 3, 'Jaun288', 'jaun6@empresa.com', '$2y$12$L9.YD/HxvdZ2TF2bBwucBunV2e58j4Q4x3Bb9skIdN4o9MBGa48pu', '2025-10-14 17:17:47', '2025-10-14 17:20:49', 5, 5, '2025-10-14 17:17:47', 1, NULL, '339', NULL, NULL, NULL, NULL),
(24, 3, 'Hernesto', 'Hernesto@empresa.com', '$2y$12$//y/nkrr7bNUYL8rUxVqR.ZTmd49srApQXs4EhTaes9pS6tMGPAEm', '2025-10-14 17:26:03', '2025-10-14 17:26:03', 5, 5, '2025-10-14 17:26:03', 3, NULL, '99', NULL, NULL, NULL, NULL);

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
  ADD KEY `id_recurso` (`id_recurso`),
  ADD KEY `fk_detalle_estado_prestamo` (`id_estado_prestamo`);

--
-- Indices de la tabla `estado`
--
ALTER TABLE `estado`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `estado_incidente`
--
ALTER TABLE `estado_incidente`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre_estado` (`nombre_estado`);

--
-- Indices de la tabla `estado_prestamo`
--
ALTER TABLE `estado_prestamo`
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
  ADD KEY `id_usuario_modificacion` (`id_usuario_modificacion`),
  ADD KEY `fk_incidente_trabajador` (`id_trabajador`),
  ADD KEY `fk_incidente_serie` (`id_serie_recurso`);

--
-- Indices de la tabla `incidente_detalle`
--
ALTER TABLE `incidente_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_incidente` (`id_incidente`),
  ADD KEY `id_serie` (`id_serie`);

--
-- Indices de la tabla `incidente_recurso`
--
ALTER TABLE `incidente_recurso`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_incidente` (`id_incidente`),
  ADD KEY `id_recurso` (`id_recurso`),
  ADD KEY `id_serie_recurso` (`id_serie_recurso`);

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
  ADD KEY `id_incidente_detalle` (`id_incidente_detalle`),
  ADD KEY `index_estado` (`id_estado`);

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
-- AUTO_INCREMENT de la tabla `detalle_prestamo`
--
ALTER TABLE `detalle_prestamo`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `estado_incidente`
--
ALTER TABLE `estado_incidente`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `estado_prestamo`
--
ALTER TABLE `estado_prestamo`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
-- AUTO_INCREMENT de la tabla `incidente`
--
ALTER TABLE `incidente`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `incidente_recurso`
--
ALTER TABLE `incidente_recurso`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `prestamo`
--
ALTER TABLE `prestamo`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `recurso`
--
ALTER TABLE `recurso`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `serie_recurso`
--
ALTER TABLE `serie_recurso`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de la tabla `stock`
--
ALTER TABLE `stock`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `subcategoria`
--
ALTER TABLE `subcategoria`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

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
  ADD CONSTRAINT `detalle_prestamo_ibfk_3` FOREIGN KEY (`id_recurso`) REFERENCES `recurso` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detalle_estado_prestamo` FOREIGN KEY (`id_estado_prestamo`) REFERENCES `estado_prestamo` (`id`);

--
-- Filtros para la tabla `incidente`
--
ALTER TABLE `incidente`
  ADD CONSTRAINT `fk_incidente_serie` FOREIGN KEY (`id_serie_recurso`) REFERENCES `serie_recurso` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_incidente_trabajador` FOREIGN KEY (`id_trabajador`) REFERENCES `usuario` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_incidente_usuario_creacion` FOREIGN KEY (`id_usuario_creacion`) REFERENCES `usuario` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_incidente_usuario_modificacion` FOREIGN KEY (`id_usuario_modificacion`) REFERENCES `usuario` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `incidente_ibfk_1` FOREIGN KEY (`id_recurso`) REFERENCES `recurso` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `incidente_ibfk_2` FOREIGN KEY (`id_incidente_detalle`) REFERENCES `incidente_detalle` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `incidente_recurso`
--
ALTER TABLE `incidente_recurso`
  ADD CONSTRAINT `incidente_recurso_ibfk_1` FOREIGN KEY (`id_incidente`) REFERENCES `incidente` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `incidente_recurso_ibfk_2` FOREIGN KEY (`id_recurso`) REFERENCES `recurso` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `incidente_recurso_ibfk_3` FOREIGN KEY (`id_serie_recurso`) REFERENCES `serie_recurso` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `prestamo`
--
ALTER TABLE `prestamo`
  ADD CONSTRAINT `fk_prestamo_usuario_creacion` FOREIGN KEY (`id_usuario_creacion`) REFERENCES `usuario` (`id`),
  ADD CONSTRAINT `fk_prestamo_usuario_modificacion` FOREIGN KEY (`id_usuario_modificacion`) REFERENCES `usuario` (`id`);

--
-- Filtros para la tabla `subcategoria`
--
ALTER TABLE `subcategoria`
  ADD CONSTRAINT `fk_subcategoria_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categoria` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

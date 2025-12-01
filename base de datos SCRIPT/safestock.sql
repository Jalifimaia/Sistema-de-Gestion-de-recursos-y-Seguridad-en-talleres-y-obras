-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 01-12-2025 a las 23:50:47
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
-- Base de datos: `safestock`
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
-- Estructura de tabla para la tabla `checklist`
--

CREATE TABLE `checklist` (
  `id` int(10) UNSIGNED NOT NULL,
  `trabajador_id` int(10) UNSIGNED NOT NULL,
  `supervisor_id` int(10) UNSIGNED NOT NULL,
  `lentes` tinyint(1) NOT NULL DEFAULT 0,
  `casco` tinyint(1) NOT NULL DEFAULT 0,
  `botas` tinyint(1) NOT NULL DEFAULT 0,
  `chaleco` tinyint(1) NOT NULL DEFAULT 0,
  `guantes` tinyint(1) NOT NULL DEFAULT 0,
  `arnes` tinyint(1) NOT NULL DEFAULT 0,
  `es_en_altura` tinyint(1) NOT NULL DEFAULT 0,
  `fecha` date NOT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `hora` time DEFAULT NULL,
  `critico` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `checklist`
--

INSERT INTO `checklist` (`id`, `trabajador_id`, `supervisor_id`, `lentes`, `casco`, `botas`, `chaleco`, `guantes`, `arnes`, `es_en_altura`, `fecha`, `observaciones`, `created_at`, `updated_at`, `hora`, `critico`) VALUES
(1, 6, 5, 1, 0, 1, 1, 1, 0, 0, '2025-11-12', 'Sin observaciones', '2025-11-13 01:56:47', '2025-11-13 01:56:47', '22:56:00', 0),
(2, 6, 5, 1, 0, 1, 1, 1, 1, 0, '2025-11-19', 'GVBHN', '2025-11-19 22:35:02', '2025-11-19 22:35:02', '22:35:00', 0),
(3, 7, 5, 1, 0, 1, 1, 1, 1, 0, '2025-11-19', 'KLKJLKJL', '2025-11-19 22:39:18', '2025-11-19 22:40:42', '22:40:00', 0),
(4, 6, 8, 1, 0, 1, 1, 1, 0, 0, '2025-11-25', 'Sin observaciones', '2025-11-25 20:25:29', '2025-11-25 20:25:29', '17:25:00', 0),
(5, 7, 8, 1, 0, 1, 1, 1, 1, 1, '2025-11-25', 'pim pum', '2025-11-25 21:34:33', '2025-11-25 21:35:35', '18:35:00', 0),
(6, 6, 5, 1, 0, 1, 1, 1, 0, 0, '2025-11-26', 'Sin observaciones', '2025-11-26 22:22:54', '2025-11-26 22:22:54', '19:22:00', 0),
(7, 6, 6, 1, 1, 1, 1, 1, 1, 0, '2025-11-28', 'Sin observaciones', '2025-11-29 02:28:38', '2025-11-29 02:37:19', '23:37:00', 0),
(8, 7, 6, 1, 0, 1, 1, 1, 1, 0, '2025-11-28', 'Sin observaciones', '2025-11-29 02:30:25', '2025-11-29 02:30:25', '23:30:00', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `color`
--

CREATE TABLE `color` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `color`
--

INSERT INTO `color` (`id`, `nombre`) VALUES
(23, '12'),
(15, '14'),
(17, '16'),
(19, '18'),
(21, '2'),
(22, '4'),
(24, '9'),
(3, 'Amarillo'),
(18, 'Anaranjado'),
(12, 'Gris'),
(14, 'Lila'),
(5, 'Marron'),
(1, 'Naranja'),
(4, 'Negro'),
(16, 'Negro oscuro'),
(2, 'Rojo'),
(9, 'Rosa');

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
(1, 1, 37, 7, 5, NULL, NULL),
(2, 2, 69, 8, 3, NULL, NULL),
(3, 3, 9, 2, 5, NULL, NULL),
(4, 4, 6, 1, 5, NULL, NULL),
(5, 5, 29, 7, 5, NULL, NULL),
(6, 6, 37, 7, 3, NULL, NULL),
(7, 7, 37, 7, 3, NULL, NULL),
(8, 8, 37, 7, 3, NULL, NULL),
(9, 9, 61, 8, 3, NULL, NULL),
(10, 10, 63, 8, 3, NULL, NULL),
(11, 11, 62, 8, 3, NULL, NULL),
(12, 12, 63, 8, 2, NULL, NULL),
(13, 13, 29, 7, 2, NULL, NULL),
(14, 14, 61, 8, 2, NULL, NULL),
(15, 15, 72, 8, 2, NULL, NULL),
(16, 16, 38, 7, 2, NULL, NULL),
(17, 17, 71, 8, 2, NULL, NULL),
(18, 18, 64, 8, 2, NULL, NULL),
(19, 19, 27, 7, 2, NULL, NULL),
(20, 20, 28, 7, 2, NULL, NULL),
(21, 19, 4, 1, 2, NULL, NULL),
(22, 21, 30, 7, 2, NULL, NULL),
(23, 22, 62, 8, 2, NULL, NULL),
(24, 23, 65, 8, 2, NULL, NULL),
(25, 24, 60, 8, 2, NULL, NULL),
(26, 25, 32, 7, 2, NULL, NULL),
(27, 26, 31, 7, 2, NULL, NULL),
(28, 27, 74, 14, 2, NULL, NULL),
(29, 29, 43, 4, 2, NULL, NULL),
(30, 32, 42, 4, 2, NULL, NULL),
(31, 36, 44, 4, 2, NULL, NULL);

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
(1, NULL, NULL, 5, 7, NULL, NULL, NULL, 'Se rompio el chaleco', '2025-11-12 23:09:00', '2025-11-12 23:09:19', '2025-11-25 19:06:12', NULL, NULL, 2),
(2, NULL, NULL, 8, 6, NULL, NULL, NULL, 'El mango quedo quebrado', '2025-11-25 23:38:00', '2025-11-25 20:38:45', '2025-11-25 20:39:16', NULL, NULL, 3),
(3, NULL, NULL, 8, 6, NULL, NULL, NULL, 'Las 2 herramientas se rompieron', '2025-11-24 21:41:00', '2025-11-25 21:41:09', '2025-11-25 21:41:09', NULL, NULL, 2);

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `incidente_recurso`
--

CREATE TABLE `incidente_recurso` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_incidente` int(10) UNSIGNED NOT NULL,
  `id_recurso` int(10) UNSIGNED NOT NULL,
  `id_serie_recurso` int(10) UNSIGNED DEFAULT NULL,
  `id_estado` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `incidente_recurso`
--

INSERT INTO `incidente_recurso` (`id`, `id_incidente`, `id_recurso`, `id_serie_recurso`, `id_estado`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 10, 6, '2025-11-25 22:06:12', '2025-11-25 22:06:12'),
(2, 2, 7, 31, 5, '2025-11-25 23:39:16', '2025-11-25 23:39:16'),
(3, 3, 8, 71, 6, '2025-11-26 00:41:09', '2025-11-26 00:41:09'),
(4, 3, 7, 37, 5, '2025-11-26 00:41:09', '2025-11-26 00:41:09');

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
(6, '2025_10_03_020124_remove_fecha_columns_from_recurso_table', 2),
(7, '2025_10_16_205155_create_prestamo_terminal_table', 3);

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
(1, 6, 6, 6, '2025-11-12 22:39:56', '2025-11-13 22:39:56', 2, '2025-11-12 22:39:56', '2025-11-12 22:39:56'),
(2, 6, 6, 6, '2025-11-12 22:42:29', '2025-11-12 22:47:51', 3, '2025-11-12 22:42:29', '2025-11-12 22:42:29'),
(3, 6, 6, 6, '2025-11-12 22:49:44', '2025-11-13 22:49:44', 2, '2025-11-12 22:49:44', '2025-11-12 22:49:44'),
(4, 6, 6, 6, '2025-11-12 22:50:51', '2025-11-13 22:50:51', 2, '2025-11-12 22:50:51', '2025-11-12 22:50:51'),
(5, 6, 6, 6, '2025-11-13 00:12:05', '2025-11-14 00:12:05', 2, '2025-11-13 00:12:05', '2025-11-13 00:12:05'),
(6, 6, 6, 6, '2025-11-14 19:21:02', '2025-11-14 19:21:44', 3, '2025-11-14 19:21:02', '2025-11-14 19:21:02'),
(7, 6, 6, 6, '2025-11-14 19:22:06', '2025-11-14 19:22:50', 3, '2025-11-14 19:22:06', '2025-11-14 19:22:06'),
(8, 6, 6, 6, '2025-11-14 19:23:12', '2025-11-15 04:28:08', 3, '2025-11-14 19:23:12', '2025-11-14 19:23:12'),
(9, 6, 6, 6, '2025-11-14 21:35:32', '2025-11-15 04:29:27', 3, '2025-11-14 21:35:32', '2025-11-14 21:35:32'),
(10, 6, 6, 6, '2025-11-14 21:49:23', '2025-11-15 04:29:45', 3, '2025-11-14 21:49:23', '2025-11-14 21:49:23'),
(11, 6, 6, 6, '2025-11-14 22:40:35', '2025-11-15 04:18:48', 3, '2025-11-14 22:40:35', '2025-11-14 22:40:35'),
(12, 6, 6, 6, '2025-11-15 04:33:29', '2025-11-16 04:33:29', 2, '2025-11-15 04:33:29', '2025-11-15 04:33:29'),
(13, 6, 6, 6, '2025-11-15 18:38:51', '2025-11-16 18:38:51', 2, '2025-11-15 18:38:51', '2025-11-15 18:38:51'),
(14, 6, 6, 6, '2025-11-15 20:38:42', '2025-11-16 20:38:42', 2, '2025-11-15 20:38:42', '2025-11-15 20:38:42'),
(15, 6, 6, 6, '2025-11-16 18:00:06', '2025-11-17 18:00:06', 2, '2025-11-16 18:00:06', '2025-11-16 18:00:06'),
(16, 6, 6, 6, '2025-11-17 04:34:55', '2025-11-18 04:34:55', 2, '2025-11-17 04:34:55', '2025-11-17 04:34:55'),
(17, 6, 6, 6, '2025-11-17 04:39:01', '2025-11-18 04:39:01', 2, '2025-11-17 04:39:01', '2025-11-17 04:39:01'),
(18, 6, 6, 6, '2025-11-17 16:46:07', '2025-11-18 16:46:07', 2, '2025-11-17 16:46:07', '2025-11-17 16:46:07'),
(19, 6, 6, 5, '2025-11-19 00:00:00', '2025-11-20 00:00:00', 2, '2025-11-19 18:38:00', '2025-11-19 22:49:30'),
(20, 6, 6, 6, '2025-11-19 18:38:06', '2025-11-20 18:38:06', 2, '2025-11-19 18:38:06', '2025-11-19 18:38:06'),
(21, 6, 6, 6, '2025-11-19 22:58:14', '2025-11-20 22:58:14', 2, '2025-11-19 22:58:14', '2025-11-19 22:58:14'),
(22, 6, 6, 6, '2025-11-19 22:58:44', '2025-11-20 22:58:44', 2, '2025-11-19 22:58:44', '2025-11-19 22:58:44'),
(23, 6, 6, 6, '2025-11-19 22:58:48', '2025-11-20 22:58:48', 2, '2025-11-19 22:58:48', '2025-11-19 22:58:48'),
(24, 6, 6, 6, '2025-11-19 22:58:51', '2025-11-20 22:58:51', 2, '2025-11-19 22:58:51', '2025-11-19 22:58:51'),
(25, 6, 6, 6, '2025-11-19 22:59:18', '2025-11-20 22:59:18', 2, '2025-11-19 22:59:18', '2025-11-19 22:59:18'),
(26, 6, 6, 6, '2025-11-19 23:00:31', '2025-11-20 23:00:31', 2, '2025-11-19 23:00:31', '2025-11-19 23:00:31'),
(27, 7, 5, 5, '2025-11-26 16:03:24', '2025-11-27 16:03:24', 2, '2025-11-26 16:03:24', '2025-11-26 16:03:24'),
(29, 6, 5, 5, '2025-11-26 16:44:09', '2025-11-27 16:44:09', 2, '2025-11-26 16:44:09', '2025-11-26 16:44:09'),
(32, 6, 5, 5, '2025-11-26 16:51:45', '2025-11-27 16:51:45', 2, '2025-11-26 16:51:45', '2025-11-26 16:51:45'),
(36, 7, 5, 5, '2025-11-26 17:05:02', '2025-11-27 17:05:02', 2, '2025-11-26 17:05:02', '2025-11-26 17:05:02');

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
  `id_subcategoria` int(11) UNSIGNED NOT NULL,
  `id_estado` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

--
-- Volcado de datos para la tabla `recurso`
--

INSERT INTO `recurso` (`id`, `id_incidente_detalle`, `id_usuario_creacion`, `id_usuario_modificacion`, `nombre`, `descripcion`, `costo_unitario`, `created_at`, `updated_at`, `id_subcategoria`, `id_estado`) VALUES
(1, NULL, 5, 5, 'Henry', 'Plastico', 40000.00, '2025-11-12 22:06:57', '2025-11-26 15:43:38', 1, NULL),
(2, NULL, 5, 5, 'Radians', 'Cinta Reflectiva', 11000.00, '2025-11-12 22:10:20', '2025-11-26 15:44:44', 3, NULL),
(3, NULL, 5, 5, 'Ombu', 'Punta de acero', 63000.00, '2025-11-12 22:10:44', '2025-11-26 15:45:34', 4, NULL),
(4, NULL, 5, 5, 'Luqstoff', 'Resistentes Anti Corte', 19599.00, '2025-11-12 22:11:19', '2025-11-26 15:47:43', 5, NULL),
(5, NULL, 5, 5, 'Steelpro', 'Transparente', 17999.00, '2025-11-12 22:11:39', '2025-11-26 15:46:41', 6, NULL),
(6, NULL, 5, 8, 'Petzl', 'Doble anclaje', 50000.00, '2025-11-12 22:12:17', '2025-11-25 18:43:19', 7, NULL),
(7, NULL, 5, 5, 'Stanley', 'Hierro', 11962.00, '2025-11-12 22:12:52', '2025-11-26 15:48:39', 2, NULL),
(8, NULL, 5, 8, 'Hamilton', 'Madera', 2000.00, '2025-11-12 22:13:12', '2025-11-25 18:43:09', 9, NULL),
(9, NULL, 5, 5, 'Walt', 'Portabrocas', 0.02, '2025-11-12 22:14:36', '2025-11-12 23:04:18', 8, 2),
(14, NULL, 5, 5, 'SKIL', 'Atornillador', 780000.00, '2025-11-26 15:41:28', '2025-11-26 15:41:28', 8, NULL),
(15, NULL, 5, 5, 'Hamilton', 'Antidesliz', 45500.00, '2025-11-26 16:42:26', '2025-11-26 16:42:26', 5, NULL),
(16, NULL, 5, 5, 'Libus', 'Amortiguador', 50230.00, '2025-11-26 17:24:07', '2025-11-26 17:24:07', 1, NULL),
(17, NULL, 5, 5, 'Foy', 'Seguridad 2 bandas', 14880.00, '2025-11-26 17:27:34', '2025-11-26 17:27:34', 3, NULL),
(18, NULL, 5, 5, 'Grafa', 'Puntera de Teflon', 54968.00, '2025-11-26 17:29:47', '2025-11-26 17:29:47', 4, NULL),
(19, NULL, 5, 5, 'DeltaPlus', 'Ajustable', 11490.00, '2025-11-26 17:33:09', '2025-11-26 17:33:09', 6, NULL),
(20, NULL, 5, 5, 'Kamasa', 'Doble punto de anclaje', 66690.00, '2025-11-26 17:34:54', '2025-11-26 17:34:54', 7, NULL);

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
-- Estructura de tabla para la tabla `serie_correlativo_counter`
--

CREATE TABLE `serie_correlativo_counter` (
  `id_serie_recurso_codigo` bigint(20) NOT NULL,
  `id_color` int(11) NOT NULL,
  `id_talle` int(11) NOT NULL,
  `counter` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `serie_recurso`
--

CREATE TABLE `serie_recurso` (
  `id` int(11) UNSIGNED NOT NULL,
  `id_recurso` int(10) UNSIGNED NOT NULL,
  `id_serie_recurso_codigo` bigint(20) UNSIGNED DEFAULT NULL,
  `id_incidente_detalle` int(10) UNSIGNED DEFAULT NULL,
  `nro_serie` varchar(30) DEFAULT NULL,
  `talle` varchar(10) DEFAULT NULL,
  `id_color` int(11) DEFAULT NULL,
  `fecha_adquisicion` datetime NOT NULL,
  `fecha_vencimiento` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `id_estado` int(11) UNSIGNED NOT NULL,
  `codigo_qr` varchar(255) DEFAULT NULL,
  `id_talle` smallint(5) UNSIGNED DEFAULT NULL,
  `correlativo` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

--
-- Volcado de datos para la tabla `serie_recurso`
--

INSERT INTO `serie_recurso` (`id`, `id_recurso`, `id_serie_recurso_codigo`, `id_incidente_detalle`, `nro_serie`, `talle`, `id_color`, `fecha_adquisicion`, `fecha_vencimiento`, `created_at`, `updated_at`, `id_estado`, `codigo_qr`, `id_talle`, `correlativo`) VALUES
(1, 1, 1, NULL, 'H-V2-P-10-02-001', NULL, 15, '2025-11-11 00:00:00', NULL, '2025-11-12 22:21:27', '2025-11-12 22:21:27', 1, 'QR-4b59308f-a8d9-435d-8c6e-9e310ef89d0e', NULL, 1),
(2, 1, 1, NULL, 'H-V2-P-10-02-002', NULL, 15, '2025-11-11 00:00:00', NULL, '2025-11-12 22:21:27', '2025-11-12 22:32:45', 3, 'QR-14698b3b-2fbe-4dc2-91a3-9db80f3714b1', NULL, 2),
(3, 1, 1, NULL, 'H-V2-P-10-02-003', NULL, 15, '2025-11-11 00:00:00', NULL, '2025-11-12 22:21:27', '2025-11-19 22:38:24', 3, 'QR-55a4734b-5ee3-4f09-bbe3-632babded7b4', NULL, 3),
(4, 1, 1, NULL, 'H-V2-P-10-02-004', NULL, 15, '2025-11-11 00:00:00', NULL, '2025-11-12 22:21:27', '2025-11-19 22:49:30', 3, 'QR-4200b778-b7ce-4e64-9663-b6af3d32bac0', NULL, 4),
(5, 1, 1, NULL, 'H-V2-P-10-02-005', NULL, 15, '2025-11-11 00:00:00', NULL, '2025-11-12 22:21:27', '2025-11-12 22:21:27', 1, 'QR-2a9eea3f-c4fc-4ddb-8cac-60868b104cda', NULL, 5),
(6, 1, 1, NULL, 'H-V2-P-10-02-006', NULL, 15, '2025-11-11 00:00:00', NULL, '2025-11-12 22:21:27', '2025-11-14 19:14:29', 1, 'QR-19f1cf16-ee18-4864-a35e-a3e0422f1484', NULL, 6),
(7, 1, 1, NULL, 'H-V2-P-10-02-007', NULL, 15, '2025-11-11 00:00:00', NULL, '2025-11-12 22:21:27', '2025-11-12 22:21:27', 1, 'QR-dffe96af-38f5-4198-b79f-b685050d6c28', NULL, 7),
(8, 1, 1, NULL, 'H-V2-P-10-02-008', NULL, 15, '2025-11-11 00:00:00', NULL, '2025-11-12 22:21:27', '2025-11-12 22:21:27', 1, 'QR-e2159513-a2ef-4bb8-9818-78827a8c3694', NULL, 8),
(9, 2, 2, NULL, 'R-V4-CR-24-06-001', '42', 23, '2025-11-12 00:00:00', NULL, '2025-11-12 22:21:59', '2025-11-14 19:14:34', 1, 'QR-d14e73bb-83ea-4d4e-ac52-a75ab29e5a0c', NULL, 1),
(10, 2, 2, NULL, 'R-V4-CR-24-06-002', '42', 23, '2025-11-12 00:00:00', NULL, '2025-11-12 22:21:59', '2025-11-25 16:06:12', 6, 'QR-6deb13cd-4e7f-46f5-9725-584ededdb8e7', NULL, 2),
(11, 2, 2, NULL, 'R-V4-CR-24-06-003', '42', 23, '2025-11-12 00:00:00', NULL, '2025-11-12 22:21:59', '2025-11-19 22:38:24', 3, 'QR-c472533b-cf6e-4b6b-be86-f4d8ec113cb6', NULL, 3),
(12, 3, 3, NULL, 'D-V6-PDA-09-03-001', '40', 21, '2025-11-12 00:00:00', NULL, '2025-11-12 22:22:34', '2025-11-12 22:22:34', 1, 'QR-befb9697-eec3-4b28-a217-b29b03bf3a0b', NULL, 1),
(13, 3, 3, NULL, 'D-V6-PDA-09-03-002', '40', 21, '2025-11-12 00:00:00', NULL, '2025-11-12 22:22:34', '2025-11-12 22:32:45', 3, 'QR-4391ecfa-70d1-4a7a-bb7e-a1badb9ca335', NULL, 2),
(14, 3, 3, NULL, 'D-V6-PDA-09-03-003', '40', 21, '2025-11-12 00:00:00', NULL, '2025-11-12 22:22:34', '2025-11-12 22:22:34', 1, 'QR-3dc630b6-fd64-43ae-a0f3-2212a9d15227', NULL, 3),
(15, 3, 3, NULL, 'D-V6-PDA-09-03-004', '40', 21, '2025-11-12 00:00:00', NULL, '2025-11-12 22:22:34', '2025-11-19 22:38:24', 3, 'QR-92adf14b-58a9-48a7-a852-736c92a09979', NULL, 4),
(16, 3, 3, NULL, 'D-V6-PDA-09-03-005', '40', 21, '2025-11-12 00:00:00', NULL, '2025-11-12 22:22:34', '2025-11-12 22:22:34', 1, 'QR-4c2d6ee4-dd1d-426a-9254-05c1152e2426', NULL, 5),
(17, 3, 3, NULL, 'D-V6-PDA-09-03-006', '40', 21, '2025-11-12 00:00:00', NULL, '2025-11-12 22:22:34', '2025-11-12 22:22:34', 1, 'QR-67903a7f-a548-4a4a-88bc-db8f3c76f6d4', NULL, 6),
(18, 3, 3, NULL, 'D-V6-PDA-09-03-007', '40', 21, '2025-11-12 00:00:00', NULL, '2025-11-12 22:22:34', '2025-11-12 22:22:34', 1, 'QR-dc67eefd-2a8b-411b-a221-811b6e8b08d4', NULL, 7),
(19, 3, 3, NULL, 'D-V6-PDA-09-03-008', '40', 21, '2025-11-12 00:00:00', NULL, '2025-11-12 22:22:34', '2025-11-12 22:22:34', 1, 'QR-45d4eb5f-07af-4b8b-aa2c-3203db2da978', NULL, 8),
(20, 3, 3, NULL, 'D-V6-PDA-09-03-009', '40', 21, '2025-11-12 00:00:00', NULL, '2025-11-12 22:22:34', '2025-11-12 22:22:34', 1, 'QR-20843003-2486-454e-bd15-886a0bab9609', NULL, 9),
(21, 3, 3, NULL, 'D-V6-PDA-09-03-010', '40', 21, '2025-11-12 00:00:00', NULL, '2025-11-12 22:22:34', '2025-11-12 22:22:34', 1, 'QR-742878ff-3c5b-402d-a88b-4f293e73bec0', NULL, 10),
(22, 3, 3, NULL, 'D-V6-PDA-09-03-011', '40', 21, '2025-11-12 00:00:00', NULL, '2025-11-12 22:22:34', '2025-11-12 22:22:34', 1, 'QR-e2e852d9-526a-4587-a138-02259859b744', NULL, 11),
(23, 3, 3, NULL, 'D-V6-PDA-09-03-012', '40', 21, '2025-11-12 00:00:00', NULL, '2025-11-12 22:22:34', '2025-11-12 22:22:34', 1, 'QR-8db8564d-b8ac-449e-96b2-f3abbc72512c', NULL, 12),
(24, 3, 3, NULL, 'D-V6-PDA-09-03-013', '40', 21, '2025-11-12 00:00:00', NULL, '2025-11-12 22:22:34', '2025-11-12 22:22:34', 1, 'QR-d2a10ef4-fb94-4800-9f97-ebcd14bba33e', NULL, 13),
(25, 3, 3, NULL, 'D-V6-PDA-09-03-014', '40', 21, '2025-11-12 00:00:00', NULL, '2025-11-12 22:22:34', '2025-11-12 22:22:34', 1, 'QR-5e1f5654-4e14-4217-83b3-e5221ec89d09', NULL, 14),
(26, 3, 3, NULL, 'D-V6-PDA-09-03-015', '40', 21, '2025-11-12 00:00:00', NULL, '2025-11-12 22:22:34', '2025-11-12 22:22:34', 1, 'QR-b22acfc5-e57d-4649-b2f3-5a9e75474dbd', NULL, 15),
(27, 7, 4, NULL, 'S-V4-H-11-06-001', NULL, 21, '2025-11-12 00:00:00', NULL, '2025-11-12 22:22:56', '2025-11-19 18:38:00', 3, 'QR-dcd4d640-8fca-40be-ba5e-990b440ef143', NULL, 1),
(28, 7, 4, NULL, 'S-V4-H-11-06-002', NULL, 21, '2025-11-12 00:00:00', NULL, '2025-11-12 22:22:56', '2025-11-19 18:38:06', 3, 'QR-18eae042-0982-40b1-b2f0-53ed885aac0b', NULL, 2),
(29, 7, 4, NULL, 'S-V4-H-11-06-003', NULL, 21, '2025-11-12 00:00:00', NULL, '2025-11-12 22:22:56', '2025-11-15 18:38:51', 3, 'QR-edc4c437-51b7-4e55-a722-76c7eef95686', NULL, 3),
(30, 7, 4, NULL, 'S-V4-H-11-06-004', NULL, 21, '2025-11-12 00:00:00', NULL, '2025-11-12 22:22:56', '2025-11-19 22:58:14', 3, 'QR-e996acba-e9be-4f2d-9937-edf6695cec88', NULL, 4),
(31, 7, 4, NULL, 'S-V4-H-11-06-005', NULL, 21, '2025-11-12 00:00:00', NULL, '2025-11-12 22:22:56', '2025-11-25 17:39:16', 5, 'QR-d2598c95-88e5-49a2-a4b3-19009c9582f5', NULL, 5),
(32, 7, 4, NULL, 'S-V4-H-11-06-006', NULL, 21, '2025-11-12 00:00:00', NULL, '2025-11-12 22:22:56', '2025-11-19 22:59:18', 3, 'QR-3584a63a-1c7b-4cdb-88f9-9a46e41b6f42', NULL, 6),
(33, 7, 4, NULL, 'S-V4-H-11-06-007', NULL, 21, '2025-11-12 00:00:00', NULL, '2025-11-12 22:22:56', '2025-11-12 22:22:56', 1, 'QR-6de84acd-3fc5-4a45-ab65-e46f5bdebb97', NULL, 7),
(34, 7, 4, NULL, 'S-V4-H-11-06-008', NULL, 21, '2025-11-12 00:00:00', NULL, '2025-11-12 22:22:56', '2025-11-12 22:22:56', 1, 'QR-10ae452a-c7b0-40e0-b13b-ec5bc7781a18', NULL, 8),
(35, 7, 4, NULL, 'S-V4-H-11-06-009', NULL, 21, '2025-11-12 00:00:00', NULL, '2025-11-12 22:22:56', '2025-11-12 22:22:56', 1, 'QR-8d64b339-c985-4970-aa3f-d8a7f825a567', NULL, 9),
(36, 7, 4, NULL, 'S-V4-H-11-06-010', NULL, 21, '2025-11-12 00:00:00', NULL, '2025-11-12 22:22:56', '2025-11-12 22:22:56', 1, 'QR-bc34f492-a4a6-4e93-a6a6-4b2f3da187ba', NULL, 10),
(37, 7, 4, NULL, 'S-V4-H-11-06-011', NULL, 21, '2025-11-12 00:00:00', NULL, '2025-11-12 22:22:56', '2025-11-25 21:41:09', 5, 'QR-3d74950f-4862-446e-9991-37abeae9f3be', NULL, 11),
(38, 7, 4, NULL, 'S-V4-H-11-06-012', NULL, 21, '2025-11-12 00:00:00', NULL, '2025-11-12 22:22:56', '2025-11-17 04:34:55', 3, 'QR-754b32dd-677b-4e2e-8fde-db683d8f914c', NULL, 12),
(39, 4, 5, NULL, 'C-V6-T-08-04-001', NULL, 24, '2025-11-12 00:00:00', NULL, '2025-11-12 22:23:23', '2025-11-12 22:23:23', 1, 'QR-023e4a09-8597-4117-af96-99179abed415', NULL, 1),
(40, 4, 5, NULL, 'C-V6-T-08-04-002', NULL, 24, '2025-11-12 00:00:00', NULL, '2025-11-12 22:23:23', '2025-11-12 22:32:45', 3, 'QR-1d5317f4-3809-42cf-8f37-737be28dc286', NULL, 2),
(41, 4, 5, NULL, 'C-V6-T-08-04-003', NULL, 24, '2025-11-12 00:00:00', NULL, '2025-11-12 22:23:23', '2025-11-19 22:38:24', 3, 'QR-2a99266e-9022-437a-b6d5-c30dfbfcb8df', NULL, 3),
(42, 4, 5, NULL, 'C-V6-T-08-04-004', NULL, 24, '2025-11-12 00:00:00', NULL, '2025-11-12 22:23:23', '2025-11-26 16:51:45', 3, 'QR-c08bde9d-3959-4d33-87f3-6de64e25b58f', NULL, 4),
(43, 4, 5, NULL, 'C-V6-T-08-04-005', NULL, 24, '2025-11-12 00:00:00', NULL, '2025-11-12 22:23:23', '2025-11-26 16:44:09', 3, 'QR-48bf820a-07bd-4146-bba6-60b2967176b6', NULL, 5),
(44, 4, 5, NULL, 'C-V6-T-08-04-006', NULL, 24, '2025-11-12 00:00:00', NULL, '2025-11-12 22:23:23', '2025-11-26 17:05:02', 3, 'QR-da0897bb-7150-4807-87e7-a7025951cc19', NULL, 6),
(45, 4, 5, NULL, 'C-V6-T-08-04-007', NULL, 24, '2025-11-12 00:00:00', NULL, '2025-11-12 22:23:23', '2025-11-12 22:23:23', 1, 'QR-2d9ad446-ce8c-4210-bffe-254a9ac49d4a', NULL, 7),
(46, 5, 6, NULL, 'P-V8-T-09-03-001', NULL, 24, '2025-11-12 00:00:00', NULL, '2025-11-12 22:23:36', '2025-11-12 22:23:36', 1, 'QR-5801e80d-7638-456b-bed1-62fdc123a73c', NULL, 1),
(47, 5, 6, NULL, 'P-V8-T-09-03-002', NULL, 24, '2025-11-12 00:00:00', NULL, '2025-11-12 22:23:36', '2025-11-12 22:32:45', 3, 'QR-dca824b3-f780-428d-b18c-3ec95bfc99d6', NULL, 2),
(48, 5, 6, NULL, 'P-V8-T-09-03-003', NULL, 24, '2025-11-12 00:00:00', NULL, '2025-11-12 22:23:36', '2025-11-19 22:38:24', 3, 'QR-8ff5ef16-b145-4bf3-aa9d-28c2407da3da', NULL, 3),
(49, 6, 7, NULL, 'P-V9-DA-09-06-001', NULL, 15, '2025-11-12 00:00:00', NULL, '2025-11-12 22:23:57', '2025-11-12 22:23:57', 1, 'QR-5a76d0f0-8af2-4d58-8bde-2558ef395473', NULL, 1),
(50, 6, 7, NULL, 'P-V9-DA-09-06-002', NULL, 15, '2025-11-12 00:00:00', NULL, '2025-11-12 22:23:57', '2025-11-12 22:23:57', 1, 'QR-e86a8200-c78e-4dbf-907b-108305740879', NULL, 2),
(51, 6, 7, NULL, 'P-V9-DA-09-06-003', NULL, 15, '2025-11-12 00:00:00', NULL, '2025-11-12 22:23:57', '2025-11-12 22:23:57', 1, 'QR-9a594736-7323-4af5-bd46-9b5d46517f10', NULL, 3),
(52, 6, 7, NULL, 'P-V9-DA-09-06-004', NULL, 15, '2025-11-12 00:00:00', NULL, '2025-11-12 22:23:57', '2025-11-19 22:38:24', 3, 'QR-295d5437-cb3e-45cd-aeab-d85be8928ae4', NULL, 4),
(53, 6, 7, NULL, 'P-V9-DA-09-06-005', NULL, 15, '2025-11-12 00:00:00', NULL, '2025-11-12 22:23:57', '2025-11-12 22:32:45', 3, 'QR-20c559f7-c2f1-409e-b281-6fb35ec3f2c0', NULL, 5),
(54, 6, 7, NULL, 'P-V9-DA-09-06-006', NULL, 15, '2025-11-12 00:00:00', NULL, '2025-11-12 22:23:57', '2025-11-12 22:23:57', 1, 'QR-b54415f5-769a-4640-b83c-f3ca45700c27', NULL, 6),
(55, 6, 7, NULL, 'P-V9-DA-09-06-007', NULL, 15, '2025-11-12 00:00:00', NULL, '2025-11-12 22:23:57', '2025-11-12 22:23:57', 1, 'QR-2947111d-74cc-4e5a-84ae-263aa6614ff1', NULL, 7),
(56, 6, 7, NULL, 'P-V9-DA-09-06-008', NULL, 15, '2025-11-12 00:00:00', NULL, '2025-11-12 22:23:57', '2025-11-12 22:23:57', 1, 'QR-bb6a2c40-23c8-43aa-8f88-4a4ed1781c7b', NULL, 8),
(57, 6, 7, NULL, 'P-V9-DA-09-06-009', NULL, 15, '2025-11-12 00:00:00', NULL, '2025-11-12 22:23:57', '2025-11-12 22:23:57', 1, 'QR-6baff3c0-d10c-456a-8489-c69896b8fbbf', NULL, 9),
(58, 6, 7, NULL, 'P-V9-DA-09-06-010', NULL, 15, '2025-11-12 00:00:00', NULL, '2025-11-12 22:23:57', '2025-11-12 22:23:57', 1, 'QR-23dc4086-4b45-4109-8cef-a94b925bc1c3', NULL, 10),
(59, 6, 7, NULL, 'P-V9-DA-09-06-011', NULL, 15, '2025-11-12 00:00:00', NULL, '2025-11-12 22:23:57', '2025-11-12 22:23:57', 1, 'QR-8a903d2d-a2e9-431a-94ef-19792bc1f071', NULL, 11),
(60, 8, 8, NULL, 'H-V2-M-11-04-001', NULL, 24, '2025-11-12 00:00:00', NULL, '2025-11-12 22:40:59', '2025-11-19 22:58:51', 3, 'QR-a1d90ab4-9f5f-4327-a0a1-75abf7a98fee', NULL, 1),
(61, 8, 8, NULL, 'H-V2-M-11-04-002', NULL, 24, '2025-11-12 00:00:00', NULL, '2025-11-12 22:40:59', '2025-11-15 20:38:42', 3, 'QR-c3622bbd-a240-41bc-b6c5-19e61559195d', NULL, 2),
(62, 8, 8, NULL, 'H-V2-M-11-04-003', NULL, 24, '2025-11-12 00:00:00', NULL, '2025-11-12 22:40:59', '2025-11-19 22:58:44', 3, 'QR-30b5969d-e9eb-4d47-b933-daf284fd9d8c', NULL, 3),
(63, 8, 8, NULL, 'H-V2-M-11-04-004', NULL, 24, '2025-11-12 00:00:00', NULL, '2025-11-12 22:40:59', '2025-11-15 04:33:29', 3, 'QR-9fa555c1-74c9-458d-82cc-3d5201bf2941', NULL, 4),
(64, 8, 8, NULL, 'H-V2-M-11-04-005', NULL, 24, '2025-11-12 00:00:00', NULL, '2025-11-12 22:40:59', '2025-11-17 16:46:07', 3, 'QR-f6e3d55d-107b-4789-969a-b296f0d2e8bd', NULL, 5),
(65, 8, 8, NULL, 'H-V2-M-11-04-006', NULL, 24, '2025-11-12 00:00:00', NULL, '2025-11-12 22:40:59', '2025-11-19 22:58:48', 3, 'QR-81c2e397-b738-4ed2-9a82-42659e8efa6f', NULL, 6),
(66, 8, 8, NULL, 'H-V2-M-11-04-007', NULL, 24, '2025-11-12 00:00:00', NULL, '2025-11-12 22:40:59', '2025-11-12 22:40:59', 1, 'QR-e94b78f8-978c-4369-b4bb-0965e85d0703', NULL, 7),
(67, 8, 8, NULL, 'H-V2-M-11-04-008', NULL, 24, '2025-11-12 00:00:00', NULL, '2025-11-12 22:40:59', '2025-11-12 22:40:59', 1, 'QR-c9435ff8-c2ce-4839-99f4-5b88e968fb00', NULL, 8),
(68, 8, 8, NULL, 'H-V2-M-11-04-009', NULL, 24, '2025-11-12 00:00:00', NULL, '2025-11-12 22:40:59', '2025-11-12 22:40:59', 1, 'QR-f90df079-96b9-4314-9558-0862cb217366', NULL, 9),
(69, 8, 8, NULL, 'H-V2-M-11-04-010', NULL, 24, '2025-11-12 00:00:00', NULL, '2025-11-12 22:40:59', '2025-11-12 22:47:51', 1, 'QR-23ddeec7-d6c4-4d33-b1e2-9bcda3dd56e2', NULL, 10),
(70, 8, 8, NULL, 'H-V2-M-11-04-011', NULL, 24, '2025-11-12 00:00:00', NULL, '2025-11-12 22:40:59', '2025-11-12 22:40:59', 1, 'QR-fd18c4c9-ee4a-4cc0-abf9-565227c94116', NULL, 11),
(71, 8, 8, NULL, 'H-V2-M-11-04-012', NULL, 24, '2025-11-12 00:00:00', NULL, '2025-11-12 22:40:59', '2025-11-25 21:41:09', 6, 'QR-560cf575-3752-478d-80d7-1c7d87073977', NULL, 12),
(72, 8, 8, NULL, 'H-V2-M-11-04-013', NULL, 24, '2025-11-12 00:00:00', NULL, '2025-11-12 22:40:59', '2025-11-16 18:00:06', 3, 'QR-4e60c950-6590-4bff-802f-a3a6c2697f64', NULL, 13),
(73, 10, NULL, NULL, 'N-V1-SSC-07-1024-001', 'XL', 19, '2025-11-05 00:00:00', NULL, '2025-11-19 22:31:10', '2025-11-25 19:54:17', 2, 'QR-0523c465-a8c0-4cc4-8e38-366db1e775bb', NULL, 1),
(74, 14, 10, NULL, 'S-V3-A-01-01-001', NULL, 14, '2019-07-20 00:00:00', '2025-11-05 00:00:00', '2025-11-26 15:42:22', '2025-11-26 16:03:24', 3, 'QR-38670e25-e4b9-441f-a102-9f453f0f70ba', NULL, 1),
(75, 15, 11, NULL, 'H-V3-A-04-01-001', NULL, 18, '2024-01-18 00:00:00', '2025-11-07 00:00:00', '2025-11-26 16:42:58', '2025-11-26 16:42:58', 1, 'QR-6c251867-7842-4b1f-a9cc-0c121de5b364', NULL, 1),
(76, 15, 11, NULL, 'H-V3-A-04-01-002', NULL, 18, '2024-01-18 00:00:00', '2025-11-07 00:00:00', '2025-11-26 16:42:58', '2025-11-26 16:42:58', 1, 'QR-c1ea081f-5399-4bcb-aebc-95af37d5bb47', NULL, 2),
(77, 16, 12, NULL, 'L-V9-A-10-03-001', NULL, 12, '2025-11-26 00:00:00', NULL, '2025-11-26 17:24:37', '2025-11-26 17:24:37', 1, 'QR-6a9e4717-8448-44a6-a331-23188b86e82f', NULL, 1),
(78, 16, 12, NULL, 'L-V9-A-10-03-002', NULL, 12, '2025-11-26 00:00:00', NULL, '2025-11-26 17:24:37', '2025-11-26 17:24:37', 1, 'QR-d9f4e1e1-e3fb-4796-b865-552ca5d81655', NULL, 2),
(79, 16, 12, NULL, 'L-V9-A-10-03-003', NULL, 12, '2025-11-26 00:00:00', NULL, '2025-11-26 17:24:37', '2025-11-26 17:24:37', 1, 'QR-38f03241-37d7-406a-99e3-d63710f050ed', NULL, 3),
(80, 16, 12, NULL, 'L-V9-A-10-03-004', NULL, 12, '2025-11-26 00:00:00', NULL, '2025-11-26 17:24:37', '2025-11-26 17:24:37', 1, 'QR-d3d51ceb-d370-4717-a44d-c51abfa6744a', NULL, 4),
(81, 16, 12, NULL, 'L-V9-A-10-03-005', NULL, 12, '2025-11-26 00:00:00', NULL, '2025-11-26 17:24:37', '2025-11-26 17:24:37', 1, 'QR-597eddee-a996-4ce0-9f3f-6a52acff430d', NULL, 5),
(82, 16, 12, NULL, 'L-V9-A-10-03-006', NULL, 12, '2025-11-26 00:00:00', NULL, '2025-11-26 17:24:37', '2025-11-26 17:24:37', 1, 'QR-6f58b00f-61fc-41d2-a96b-357fed72a23a', NULL, 6),
(83, 16, 12, NULL, 'L-V9-A-10-03-007', NULL, 12, '2025-11-26 00:00:00', NULL, '2025-11-26 17:24:37', '2025-11-26 17:24:37', 1, 'QR-24605c0d-9e4b-462d-8c64-8715f22634cd', NULL, 7),
(84, 16, 13, NULL, 'L-V4-A-22-03-001', NULL, 3, '2025-11-26 00:00:00', NULL, '2025-11-26 17:25:16', '2025-11-26 17:25:16', 1, 'QR-9c95b54e-238d-4eb2-958d-a13e87ef8719', NULL, 1),
(85, 16, 13, NULL, 'L-V4-A-22-03-002', NULL, 3, '2025-11-26 00:00:00', NULL, '2025-11-26 17:25:16', '2025-11-26 17:25:16', 1, 'QR-3496258a-cedd-452e-8225-d3dbfc43e797', NULL, 2),
(86, 16, 13, NULL, 'L-V4-A-22-03-003', NULL, 3, '2025-11-26 00:00:00', NULL, '2025-11-26 17:25:16', '2025-11-26 17:25:16', 1, 'QR-3b6612b2-11b1-4804-ba96-e189515f5db9', NULL, 3),
(87, 16, 13, NULL, 'L-V4-A-22-03-004', NULL, 3, '2025-11-26 00:00:00', NULL, '2025-11-26 17:25:17', '2025-11-26 17:25:17', 1, 'QR-8b0962d4-5bdf-42f5-a9e4-a3acd1a901c6', NULL, 4),
(88, 16, 13, NULL, 'L-V4-A-22-03-005', NULL, 3, '2025-11-26 00:00:00', NULL, '2025-11-26 17:25:17', '2025-11-26 17:25:17', 1, 'QR-6ba88889-b4db-43c0-a9c0-9462158863bb', NULL, 5),
(89, 16, 13, NULL, 'L-V4-A-22-03-006', NULL, 3, '2025-11-26 00:00:00', NULL, '2025-11-26 17:25:17', '2025-11-26 17:25:17', 1, 'QR-1912a036-7320-4b8e-8809-14342e42edd2', NULL, 6),
(90, 16, 13, NULL, 'L-V4-A-22-03-007', NULL, 3, '2025-11-26 00:00:00', NULL, '2025-11-26 17:25:17', '2025-11-26 17:25:17', 1, 'QR-0ea3ca05-94e6-4fec-92d5-8a7fb8f1f533', NULL, 7),
(91, 17, 14, NULL, 'F-V4-S2B-24-03-001', 'S', 1, '2025-11-26 00:00:00', NULL, '2025-11-26 17:28:27', '2025-11-26 17:28:27', 1, 'QR-85a193a3-c814-4f59-82cd-81bc44a01886', NULL, 1),
(92, 17, 14, NULL, 'F-V4-S2B-24-03-002', 'S', 1, '2025-11-26 00:00:00', NULL, '2025-11-26 17:28:27', '2025-11-26 17:28:27', 1, 'QR-e1abddcb-1c10-41bd-bd73-4fefea7440ec', NULL, 2),
(93, 17, 14, NULL, 'F-V4-S2B-24-03-003', 'S', 1, '2025-11-26 00:00:00', NULL, '2025-11-26 17:28:27', '2025-11-26 17:28:27', 1, 'QR-240e8fd1-b3ef-4d22-96bb-0ee9dc3998cd', NULL, 3),
(94, 17, 14, NULL, 'F-V4-S2B-24-03-004', 'S', 1, '2025-11-26 00:00:00', NULL, '2025-11-26 17:28:27', '2025-11-26 17:28:27', 1, 'QR-607246b4-5d8a-48cc-8357-1d7b403d4704', NULL, 4),
(95, 17, 14, NULL, 'F-V4-S2B-24-03-005', 'S', 1, '2025-11-26 00:00:00', NULL, '2025-11-26 17:28:27', '2025-11-26 17:28:27', 1, 'QR-51a99cab-eb00-410f-b2db-d1bc1bb1c57b', NULL, 5),
(96, 17, 14, NULL, 'F-V4-S2B-24-03-006', 'S', 1, '2025-11-26 00:00:00', NULL, '2025-11-26 17:28:27', '2025-11-26 17:28:27', 1, 'QR-feda1bf5-7b56-4e95-8a3f-feb7dec6b44e', NULL, 6),
(97, 17, 14, NULL, 'F-V4-S2B-24-03-007', 'S', 1, '2025-11-26 00:00:00', NULL, '2025-11-26 17:28:27', '2025-11-26 17:28:27', 1, 'QR-3899cb56-9d2e-415f-9da9-c7383911eede', NULL, 7),
(98, 17, 14, NULL, 'F-V4-S2B-24-03-008', 'S', 1, '2025-11-26 00:00:00', NULL, '2025-11-26 17:28:27', '2025-11-26 17:28:27', 1, 'QR-ae5e1851-47e4-402e-8d43-728b16ed5cae', NULL, 8),
(99, 17, 14, NULL, 'F-V4-S2B-24-03-009', 'S', 1, '2025-11-26 00:00:00', NULL, '2025-11-26 17:28:27', '2025-11-26 17:28:27', 1, 'QR-3632b79b-564c-49be-8ac6-88bc6ccf4210', NULL, 9),
(100, 17, 14, NULL, 'F-V4-S2B-24-03-010', 'S', 1, '2025-11-26 00:00:00', NULL, '2025-11-26 17:28:27', '2025-11-26 17:28:27', 1, 'QR-bfe5efc0-6f82-45f0-a6b7-1dde385f4eaf', NULL, 10),
(101, 17, 14, NULL, 'F-V4-S2B-24-03-001', 'M', 1, '2025-11-26 00:00:00', NULL, '2025-11-26 17:28:27', '2025-11-26 17:28:27', 1, 'QR-f3fb3630-9f01-4c48-8d33-5605f8459184', NULL, 1),
(102, 17, 14, NULL, 'F-V4-S2B-24-03-002', 'M', 1, '2025-11-26 00:00:00', NULL, '2025-11-26 17:28:27', '2025-11-26 17:28:27', 1, 'QR-93d8f7a2-3a86-4b66-b9a4-ebb1242a958f', NULL, 2),
(103, 17, 14, NULL, 'F-V4-S2B-24-03-003', 'M', 1, '2025-11-26 00:00:00', NULL, '2025-11-26 17:28:27', '2025-11-26 17:28:27', 1, 'QR-91f25ce8-29f0-49e1-9f02-9e32d85f19e7', NULL, 3),
(104, 17, 14, NULL, 'F-V4-S2B-24-03-004', 'M', 1, '2025-11-26 00:00:00', NULL, '2025-11-26 17:28:27', '2025-11-26 17:28:27', 1, 'QR-d78230b8-953b-4662-8dae-d76cf61e0394', NULL, 4),
(105, 17, 14, NULL, 'F-V4-S2B-24-03-005', 'M', 1, '2025-11-26 00:00:00', NULL, '2025-11-26 17:28:27', '2025-11-26 17:28:27', 1, 'QR-7d7b4459-b2f3-4cef-b5c6-48df0e8e1fb6', NULL, 5),
(106, 17, 14, NULL, 'F-V4-S2B-24-03-006', 'M', 1, '2025-11-26 00:00:00', NULL, '2025-11-26 17:28:27', '2025-11-26 17:28:27', 1, 'QR-bbef90fd-a201-4dbc-aaf0-40d3c422f546', NULL, 6),
(107, 17, 14, NULL, 'F-V4-S2B-24-03-007', 'M', 1, '2025-11-26 00:00:00', NULL, '2025-11-26 17:28:27', '2025-11-26 17:28:27', 1, 'QR-9c9220d1-ecd2-42da-a1b4-0a5abbfd5815', NULL, 7),
(108, 17, 14, NULL, 'F-V4-S2B-24-03-008', 'M', 1, '2025-11-26 00:00:00', NULL, '2025-11-26 17:28:27', '2025-11-26 17:28:27', 1, 'QR-edea5383-8d69-4d77-a26e-e8a9c8313f6b', NULL, 8),
(109, 17, 14, NULL, 'F-V4-S2B-24-03-009', 'M', 1, '2025-11-26 00:00:00', NULL, '2025-11-26 17:28:27', '2025-11-26 17:28:27', 1, 'QR-1835ff23-4b93-49d2-97ed-9a12d398a93e', NULL, 9),
(110, 17, 14, NULL, 'F-V4-S2B-24-03-010', 'M', 1, '2025-11-26 00:00:00', NULL, '2025-11-26 17:28:27', '2025-11-26 17:28:27', 1, 'QR-54ed8c0a-d917-4f16-9a4c-df4e07948bf8', NULL, 10),
(111, 18, 15, NULL, 'G-V3-PDT-23-02-001', '38', 5, '2025-11-26 00:00:00', NULL, '2025-11-26 17:30:44', '2025-11-26 17:30:44', 1, 'QR-4812cc9e-8af5-4c33-a733-539e74c26817', NULL, 1),
(112, 18, 15, NULL, 'G-V3-PDT-23-02-002', '38', 5, '2025-11-26 00:00:00', NULL, '2025-11-26 17:30:44', '2025-11-26 17:30:44', 1, 'QR-65a7af31-8588-4fcb-bdf8-11e67b111f43', NULL, 2),
(113, 18, 15, NULL, 'G-V3-PDT-23-02-003', '38', 5, '2025-11-26 00:00:00', NULL, '2025-11-26 17:30:44', '2025-11-26 17:30:44', 1, 'QR-2f9cc13d-8729-461b-bc82-0890a63f3797', NULL, 3),
(114, 18, 15, NULL, 'G-V3-PDT-23-02-004', '38', 5, '2025-11-26 00:00:00', NULL, '2025-11-26 17:30:44', '2025-11-26 17:30:44', 1, 'QR-598b9e78-89b3-404c-b8c3-e6ef7628d745', NULL, 4),
(115, 18, 15, NULL, 'G-V3-PDT-23-02-005', '38', 5, '2025-11-26 00:00:00', NULL, '2025-11-26 17:30:44', '2025-11-26 17:30:44', 1, 'QR-35009ea6-2de1-4f5a-83f9-eb541ce190ca', NULL, 5),
(116, 18, 15, NULL, 'G-V3-PDT-23-02-001', '39', 12, '2025-11-26 00:00:00', NULL, '2025-11-26 17:30:44', '2025-11-26 17:30:44', 1, 'QR-17fb9ba0-d12a-432e-b4d6-1cb411270878', NULL, 1),
(117, 18, 15, NULL, 'G-V3-PDT-23-02-002', '39', 12, '2025-11-26 00:00:00', NULL, '2025-11-26 17:30:44', '2025-11-26 17:30:44', 1, 'QR-d3b84e62-2548-4249-8560-a6a908f8c376', NULL, 2),
(118, 18, 15, NULL, 'G-V3-PDT-23-02-003', '39', 12, '2025-11-26 00:00:00', NULL, '2025-11-26 17:30:44', '2025-11-26 17:30:44', 1, 'QR-cdb03a3d-7b83-4340-89b2-92b25c38378e', NULL, 3),
(119, 18, 15, NULL, 'G-V3-PDT-23-02-004', '39', 12, '2025-11-26 00:00:00', NULL, '2025-11-26 17:30:44', '2025-11-26 17:30:44', 1, 'QR-be505c9e-0b06-4b50-84ec-76bee755ce70', NULL, 4),
(120, 18, 15, NULL, 'G-V3-PDT-23-02-005', '39', 12, '2025-11-26 00:00:00', NULL, '2025-11-26 17:30:44', '2025-11-26 17:30:44', 1, 'QR-f3edf987-5415-4e21-90a4-3a1c8cec2584', NULL, 5);

--
-- Disparadores `serie_recurso`
--
DELIMITER $$
CREATE TRIGGER `trg_serie_recurso_before_insert` BEFORE INSERT ON `serie_recurso` FOR EACH ROW BEGIN
  DECLARE base VARCHAR(200);
  IF NEW.id_serie_recurso_codigo IS NOT NULL THEN
    SELECT codigo_base INTO base
      FROM serie_recurso_codigo
     WHERE id = NEW.id_serie_recurso_codigo
     LIMIT 1;
    SET NEW.nro_serie = CONCAT(base, '-', LPAD(COALESCE(NEW.correlativo, 0), 3, '0'));
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_serie_recurso_before_update` BEFORE UPDATE ON `serie_recurso` FOR EACH ROW BEGIN
  DECLARE base VARCHAR(200);
  IF NEW.id_serie_recurso_codigo IS NOT NULL THEN
    SELECT codigo_base INTO base
      FROM serie_recurso_codigo
     WHERE id = NEW.id_serie_recurso_codigo
     LIMIT 1;
    SET NEW.nro_serie = CONCAT(base, '-', LPAD(COALESCE(NEW.correlativo, 0), 3, '0'));
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `serie_recurso_codigo`
--

CREATE TABLE `serie_recurso_codigo` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_recurso` int(10) UNSIGNED NOT NULL,
  `version` smallint(6) NOT NULL,
  `anio` smallint(6) NOT NULL,
  `lote` smallint(6) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `codigo_base` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `serie_recurso_codigo`
--

INSERT INTO `serie_recurso_codigo` (`id`, `id_recurso`, `version`, `anio`, `lote`, `created_at`, `updated_at`, `codigo_base`) VALUES
(1, 1, 2, 2010, 2, '2025-11-13 01:21:26', '2025-11-13 01:21:26', 'H-V2-P-10-02'),
(2, 2, 4, 2024, 6, '2025-11-13 01:21:59', '2025-11-13 01:21:59', 'R-V4-CR-24-06'),
(3, 3, 6, 2009, 3, '2025-11-13 01:22:34', '2025-11-13 01:22:34', 'D-V6-PDA-09-03'),
(4, 7, 4, 2011, 6, '2025-11-13 01:22:56', '2025-11-13 01:22:56', 'S-V4-H-11-06'),
(5, 4, 6, 2008, 4, '2025-11-13 01:23:23', '2025-11-13 01:23:23', 'C-V6-T-08-04'),
(6, 5, 8, 2009, 3, '2025-11-13 01:23:36', '2025-11-13 01:23:36', 'P-V8-T-09-03'),
(7, 6, 9, 2009, 6, '2025-11-13 01:23:57', '2025-11-13 01:23:57', 'P-V9-DA-09-06'),
(8, 8, 2, 2011, 4, '2025-11-13 01:40:59', '2025-11-13 01:40:59', 'H-V2-M-11-04'),
(10, 14, 3, 2001, 1, '2025-11-26 18:42:22', '2025-11-26 18:42:22', 'S-V3-A-01-01'),
(11, 15, 3, 2004, 1, '2025-11-26 19:42:58', '2025-11-26 19:42:58', 'H-V3-A-04-01'),
(12, 16, 9, 2010, 3, '2025-11-26 20:24:37', '2025-11-26 20:24:37', 'L-V9-A-10-03'),
(13, 16, 4, 2022, 3, '2025-11-26 20:25:16', '2025-11-26 20:25:16', 'L-V4-A-22-03'),
(14, 17, 4, 2024, 3, '2025-11-26 20:28:27', '2025-11-26 20:28:27', 'F-V4-S2B-24-03'),
(15, 18, 3, 2023, 2, '2025-11-26 20:30:44', '2025-11-26 20:30:44', 'G-V3-PDT-23-02');

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
(1, 7, 37, 1, NULL),
(2, 8, 69, 1, NULL),
(3, 2, 9, 3, 6),
(4, 1, 6, 3, 6),
(5, 7, 29, 3, 6),
(6, 8, 61, 3, 6),
(7, 8, 63, 3, 6),
(8, 8, 62, 3, 6),
(9, 8, 72, 3, 6),
(10, 7, 38, 3, 6),
(11, 8, 71, 3, 6),
(12, 8, 64, 3, 6),
(13, 7, 27, 3, 6),
(14, 7, 28, 3, 6),
(15, 1, 4, 3, 6),
(16, 7, 30, 3, 6),
(17, 8, 65, 3, 6),
(18, 8, 60, 3, 6),
(19, 7, 32, 3, 6),
(20, 7, 31, 3, 6),
(21, 14, 74, 3, 7),
(22, 4, 43, 3, 6),
(23, 4, 42, 3, 6),
(24, 4, 44, 3, 7);

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
(1, 'Casco', 1),
(2, 'Martillo', 2),
(3, 'Chaleco', 1),
(4, 'Botas', 1),
(5, 'Guantes', 1),
(6, 'Lentes', 1),
(7, 'Arnes', 1),
(8, 'Taladro', 2),
(9, 'Serrucho', 2),
(10, 'Sierra', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `talle`
--

CREATE TABLE `talle` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `tipo` enum('ropa','calzado','otro') DEFAULT 'otro',
  `nombre` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `talle`
--

INSERT INTO `talle` (`id`, `tipo`, `nombre`) VALUES
(7, 'ropa', '3XL'),
(8, 'ropa', '4XL'),
(4, 'ropa', 'L'),
(3, 'ropa', 'M'),
(2, 'ropa', 'S'),
(5, 'ropa', 'XL'),
(1, 'ropa', 'XS'),
(6, 'ropa', 'XXL'),
(9, 'calzado', '35'),
(10, 'calzado', '36'),
(11, 'calzado', '37'),
(12, 'calzado', '38'),
(13, 'calzado', '39'),
(14, 'calzado', '40'),
(15, 'calzado', '41'),
(16, 'calzado', '42'),
(17, 'calzado', '43'),
(18, 'calzado', '44'),
(19, 'calzado', '45'),
(20, 'calzado', '46'),
(22, 'otro', 'Adulto'),
(23, 'otro', 'Grande'),
(21, 'otro', 'Único');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tareas_diarias`
--

CREATE TABLE `tareas_diarias` (
  `id` int(10) UNSIGNED NOT NULL,
  `trabajador_id` int(10) UNSIGNED NOT NULL,
  `fecha` date NOT NULL,
  `requiere_altura` tinyint(1) NOT NULL DEFAULT 0,
  `asignado_por` int(10) UNSIGNED NOT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `access_token` varchar(255) DEFAULT NULL,
  `codigo_qr` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id`, `id_rol`, `name`, `email`, `password`, `created_at`, `updated_at`, `usuario_creacion`, `usuario_modificacion`, `ultimo_acceso`, `id_estado`, `fecha_nacimiento`, `dni`, `telefono`, `nro_legajo`, `auth_key`, `access_token`, `codigo_qr`) VALUES
(5, 1, 'Micaela Barroso', 'micaela@empresa.com', '$2y$12$IE9ataL.KjMhHx8QjFB/eOROZfKFOxa2/qZ2evSu.eBPLU.yvGq6q', '2025-10-03 18:08:23', '2025-12-01 19:49:47', NULL, 8, '2025-12-01 19:49:47', 1, NULL, '44259684', NULL, NULL, NULL, NULL, 'USR-722c6a13-ad73-11f0-a94d-00e070eec074'),
(6, 3, 'David Cardozo', 'david@empresa.com', '$2y$12$b2Pm5.EsG.CjCi.bQON/B.2ZR1EnPxr3ONawUL9yeMamRJSzKcbeG', '2025-11-12 22:31:02', '2025-11-29 11:57:19', 5, 8, '2025-11-29 11:57:19', 1, NULL, '42229249', NULL, NULL, NULL, NULL, 'USR-722c830f-ad73-11f0-a94d-00e070eec074'),
(7, 3, 'Maia Jalifi', 'maia@empresa.com', '$2y$12$8ZMPn421aeKCdbjeOEiIRuP8LuRl0/yXKds.77PtekSjfJW7BgGl2', '2025-11-12 22:52:38', '2025-11-25 17:33:06', 5, 8, '2025-11-25 16:11:10', 1, NULL, '46196964', NULL, NULL, NULL, NULL, 'USR-ebb10c71-fb9b-42a8-863b-290d4cbf5398'),
(8, 1, 'Gaston Roa', 'gaston@empresa.com', '$2y$12$dzsBvGNfoBGWZ69ODb1DhuqIATU8VC.YuOi/OG5z6qEnOM3fMzj/W', '2025-11-19 05:47:43', '2025-11-26 13:22:02', 5, 8, '2025-11-26 13:22:02', 1, NULL, '40429104', NULL, NULL, NULL, NULL, 'USR-0511bb6b-6a0c-4356-bd01-ae64574276e8'),
(9, 2, 'Argañaras Anabela', 'anabela@empresa.com', '$2y$12$xREDzp6sCmU3dbfS27kZnOYrjS.2paVcXDEULp0sag8TAbFbhmMJW', '2025-11-19 22:45:14', '2025-11-29 14:41:25', 5, 8, '2025-11-29 14:41:25', 1, NULL, '43507420', NULL, NULL, NULL, NULL, 'USR-cdaaf5fb-8daf-44da-b8dd-b0c76c0e6488'),
(11, 3, 'Invitado', 'invitado@empresa.com', '$2y$12$jzTZAVS6cQBM.YFOLR1UCO4g1M38bz.yzbogOa7cwYlvEPRMEfX6.', '2025-12-01 18:34:24', '2025-12-01 18:35:01', 5, 5, '2025-12-01 18:34:23', 1, NULL, '1', NULL, NULL, NULL, NULL, 'USR-dfcb7c3a-b1b2-488e-817d-2fbe2984c039');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_recurso`
--

CREATE TABLE `usuario_recurso` (
  `id` int(10) NOT NULL,
  `id_usuario` int(10) NOT NULL,
  `id_recurso` int(10) NOT NULL,
  `tipo_epp` varchar(50) DEFAULT NULL,
  `id_serie_recurso` int(10) UNSIGNED DEFAULT NULL,
  `fecha_asignacion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario_recurso`
--

INSERT INTO `usuario_recurso` (`id`, `id_usuario`, `id_recurso`, `tipo_epp`, `id_serie_recurso`, `fecha_asignacion`) VALUES
(1, 6, 1, 'casco', 2, '2025-11-12 00:00:00'),
(2, 6, 4, 'guantes', 40, '2025-11-12 00:00:00'),
(3, 6, 5, 'lentes', 47, '2025-11-12 00:00:00'),
(4, 6, 3, 'botas', 13, '2025-11-12 00:00:00'),
(5, 6, 2, 'chaleco', 10, '2025-11-12 00:00:00'),
(6, 6, 6, 'arnes', 53, '2025-11-12 00:00:00'),
(7, 7, 1, 'casco', 3, '2025-11-19 00:00:00'),
(8, 7, 4, 'guantes', 41, '2025-11-19 00:00:00'),
(9, 7, 5, 'lentes', 48, '2025-11-19 00:00:00'),
(10, 7, 3, 'botas', 15, '2025-11-19 00:00:00'),
(11, 7, 2, 'chaleco', 11, '2025-11-19 00:00:00'),
(12, 7, 6, 'arnes', 52, '2025-11-19 00:00:00');

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
-- Indices de la tabla `checklist`
--
ALTER TABLE `checklist`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `color`
--
ALTER TABLE `color`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

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
  ADD KEY `id_serie_recurso` (`id_serie_recurso`),
  ADD KEY `fk_incidente_recurso_estado` (`id_estado`);

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
  ADD KEY `fk_subcategoria` (`id_subcategoria`),
  ADD KEY `fk_recurso_estado` (`id_estado`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `serie_correlativo_counter`
--
ALTER TABLE `serie_correlativo_counter`
  ADD PRIMARY KEY (`id_serie_recurso_codigo`,`id_color`,`id_talle`);

--
-- Indices de la tabla `serie_recurso`
--
ALTER TABLE `serie_recurso`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_qr` (`codigo_qr`),
  ADD UNIQUE KEY `codigo_qr_2` (`codigo_qr`),
  ADD KEY `id_recurso` (`id_recurso`),
  ADD KEY `id_incidente_detalle` (`id_incidente_detalle`),
  ADD KEY `index_estado` (`id_estado`),
  ADD KEY `fk_serie_color` (`id_color`),
  ADD KEY `ix_codigo_correlativo` (`id_serie_recurso_codigo`,`id_color`,`id_talle`,`correlativo`),
  ADD KEY `fk_serie_talle` (`id_talle`);

--
-- Indices de la tabla `serie_recurso_codigo`
--
ALTER TABLE `serie_recurso_codigo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_recurso_version_anio_lote` (`id_recurso`,`version`,`anio`,`lote`);

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
-- Indices de la tabla `talle`
--
ALTER TABLE `talle`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_talle_tipo_nombre` (`tipo`,`nombre`);

--
-- Indices de la tabla `tareas_diarias`
--
ALTER TABLE `tareas_diarias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trabajador_id` (`trabajador_id`),
  ADD KEY `asignado_por` (`asignado_por`);

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
  ADD UNIQUE KEY `codigo_qr` (`codigo_qr`),
  ADD KEY `id_rol` (`id_rol`),
  ADD KEY `idx_usuario_creacion` (`usuario_creacion`),
  ADD KEY `idx_usuario_modificacion` (`usuario_modificacion`),
  ADD KEY `id_estado` (`id_estado`);

--
-- Indices de la tabla `usuario_recurso`
--
ALTER TABLE `usuario_recurso`
  ADD PRIMARY KEY (`id`),
  ADD KEY `index_usuario` (`id_usuario`,`id_recurso`),
  ADD KEY `fk_usuario_recurso_serie` (`id_serie_recurso`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `checklist`
--
ALTER TABLE `checklist`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `color`
--
ALTER TABLE `color`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `detalle_prestamo`
--
ALTER TABLE `detalle_prestamo`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `incidente_recurso`
--
ALTER TABLE `incidente_recurso`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `prestamo`
--
ALTER TABLE `prestamo`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de la tabla `recurso`
--
ALTER TABLE `recurso`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `serie_recurso`
--
ALTER TABLE `serie_recurso`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT de la tabla `serie_recurso_codigo`
--
ALTER TABLE `serie_recurso_codigo`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `stock`
--
ALTER TABLE `stock`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `subcategoria`
--
ALTER TABLE `subcategoria`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `talle`
--
ALTER TABLE `talle`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `tareas_diarias`
--
ALTER TABLE `tareas_diarias`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `usuario_recurso`
--
ALTER TABLE `usuario_recurso`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

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
  ADD CONSTRAINT `fk_incidente_recurso_estado` FOREIGN KEY (`id_estado`) REFERENCES `estado` (`id`) ON DELETE SET NULL,
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
-- Filtros para la tabla `recurso`
--
ALTER TABLE `recurso`
  ADD CONSTRAINT `fk_recurso_estado` FOREIGN KEY (`id_estado`) REFERENCES `estado` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `serie_recurso`
--
ALTER TABLE `serie_recurso`
  ADD CONSTRAINT `fk_serie_codigo` FOREIGN KEY (`id_serie_recurso_codigo`) REFERENCES `serie_recurso_codigo` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_serie_color` FOREIGN KEY (`id_color`) REFERENCES `color` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_serie_talle` FOREIGN KEY (`id_talle`) REFERENCES `talle` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `serie_recurso_codigo`
--
ALTER TABLE `serie_recurso_codigo`
  ADD CONSTRAINT `fk_src_recurso` FOREIGN KEY (`id_recurso`) REFERENCES `recurso` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `subcategoria`
--
ALTER TABLE `subcategoria`
  ADD CONSTRAINT `fk_subcategoria_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categoria` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `tareas_diarias`
--
ALTER TABLE `tareas_diarias`
  ADD CONSTRAINT `tareas_diarias_ibfk_1` FOREIGN KEY (`trabajador_id`) REFERENCES `usuario` (`id`),
  ADD CONSTRAINT `tareas_diarias_ibfk_2` FOREIGN KEY (`asignado_por`) REFERENCES `usuario` (`id`);

--
-- Filtros para la tabla `usuario_recurso`
--
ALTER TABLE `usuario_recurso`
  ADD CONSTRAINT `fk_usuario_recurso_serie` FOREIGN KEY (`id_serie_recurso`) REFERENCES `serie_recurso` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

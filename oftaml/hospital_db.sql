-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 17-01-2026 a las 04:45:11
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
-- Base de datos: `hospital_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `archivos_historial`
--

CREATE TABLE `archivos_historial` (
  `id` int(11) NOT NULL,
  `id_historial` int(11) DEFAULT NULL COMMENT 'Referencia opcional a historial_clinico',
  `id_paciente` int(11) NOT NULL,
  `id_usuario_subida` int(11) NOT NULL,
  `id_categoria` int(11) DEFAULT NULL,
  `nombre_original` varchar(255) NOT NULL,
  `nombre_guardado` varchar(255) NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `archivos_historial`
--

INSERT INTO `archivos_historial` (`id`, `id_historial`, `id_paciente`, `id_usuario_subida`, `id_categoria`, `nombre_original`, `nombre_guardado`, `ruta_archivo`, `fecha_subida`) VALUES
(34, 45, 8, 1, 3, 'Cárcamo Romero Justo Cesar.pdf', 'doc_69212cc922e655.25126587.pdf', 'uploads/historial/paciente_8/', '2025-11-22 03:23:53'),
(53, 49, 1, 1, 1, 'Campimetria-estudio-campo-visual-prueba-medica-oftalnova-barcelona-2.jpg', 'doc_696b01e090fce1.37265582.jpg', 'uploads/historial/paciente_1_Juan_Carlos_Perez_Gomez/', '2026-01-17 03:28:32'),
(54, 49, 1, 1, 1, 'Imagen1.jpg', 'doc_696b032f1124a9.29413953.jpg', 'uploads/historial/paciente_1_Juan_Carlos_Perez_Gomez/', '2026-01-17 03:34:07'),
(55, 49, 1, 1, 1, 'MayCEFig12.jpg', 'doc_696b0346ce0851.84840709.jpg', 'uploads/historial/paciente_1_Juan_Carlos_Perez_Gomez/', '2026-01-17 03:34:30'),
(56, 49, 1, 1, 1, 'OCT.jpg', 'doc_696b0358f06599.05394600.jpg', 'uploads/historial/paciente_1_Juan_Carlos_Perez_Gomez/', '2026-01-17 03:34:48'),
(58, 49, 1, 1, 3, 'María Angela Díaz Ayala .pdf', 'doc_696b03c51d9ba4.19072605.pdf', 'uploads/historial/paciente_1_Juan_Carlos_Perez_Gomez/', '2026-01-17 03:36:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `archivo_categorias`
--

CREATE TABLE `archivo_categorias` (
  `id` int(11) NOT NULL,
  `nombre_categoria` varchar(100) NOT NULL,
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `archivo_categorias`
--

INSERT INTO `archivo_categorias` (`id`, `nombre_categoria`, `estado`) VALUES
(1, 'Éxamen', 'Activo'),
(2, 'Análisis', 'Activo'),
(3, 'Expediente Archivo', 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aseguradoras`
--

CREATE TABLE `aseguradoras` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `aseguradoras`
--

INSERT INTO `aseguradoras` (`id`, `nombre`, `estado`) VALUES
(1, 'Seguros Atlántida', 'Activo'),
(2, 'MAPFRE Honduras', 'Activo'),
(3, 'Ficohsa Seguros', 'Activo'),
(4, 'Seguros del Valle', 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias_producto`
--

CREATE TABLE `categorias_producto` (
  `id` int(11) NOT NULL,
  `nombre_categoria` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias_producto`
--

INSERT INTO `categorias_producto` (`id`, `nombre_categoria`) VALUES
(1, 'Medicamentos');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias_servicio`
--

CREATE TABLE `categorias_servicio` (
  `id` int(11) NOT NULL,
  `nombre_categoria` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias_servicio`
--

INSERT INTO `categorias_servicio` (`id`, `nombre_categoria`) VALUES
(2, 'Cirugía'),
(1, 'Consulta'),
(3, 'Procedimiento Menor');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas`
--

CREATE TABLE `citas` (
  `id` int(11) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `id_medico` int(11) DEFAULT NULL,
  `id_servicio` int(11) NOT NULL,
  `fecha_cita` date NOT NULL,
  `hora_cita` time NOT NULL,
  `estado` enum('Programada','Completada','Cancelada','No se presentó') NOT NULL DEFAULT 'Programada',
  `notificado` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0 = No, 1 = Sí',
  `facturada` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0 = No, 1 = Sí'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `citas`
--

INSERT INTO `citas` (`id`, `id_paciente`, `id_medico`, `id_servicio`, `fecha_cita`, `hora_cita`, `estado`, `notificado`, `facturada`) VALUES
(1, 1, NULL, 1, '2025-08-11', '10:00:00', 'Programada', 0, 0),
(2, 1, NULL, 1, '2025-08-07', '17:32:00', 'Programada', 1, 0),
(3, 1, NULL, 1, '2025-08-08', '15:04:00', 'Completada', 1, 1),
(4, 1, 2, 1, '2025-08-11', '10:16:00', 'Completada', 1, 1),
(5, 1, 2, 1, '2025-08-12', '15:57:00', 'Completada', 1, 1),
(6, 1, NULL, 1, '2025-08-21', '15:01:00', 'Completada', 0, 1),
(7, 1, 2, 3, '2025-08-23', '12:02:00', 'Completada', 0, 1),
(8, 1, 2, 3, '2025-08-26', '13:27:00', 'Completada', 1, 0),
(10, 3, 2, 1, '1980-05-25', '10:01:00', 'Completada', 0, 0),
(11, 3, 2, 1, '2025-11-13', '10:01:00', 'Completada', 1, 0),
(12, 4, 2, 1, '2025-11-17', '22:48:00', 'Programada', 1, 0),
(13, 2, 2, 3, '2025-11-17', '10:29:00', 'Programada', 0, 0),
(14, 1, 2, 1, '2025-11-20', '09:00:00', 'Programada', 0, 0),
(15, 1, 2, 1, '2025-11-17', '21:02:00', 'Cancelada', 0, 1),
(16, 5, 2, 1, '2025-11-17', '23:21:00', 'Programada', 0, 0),
(17, 6, 2, 1, '2025-11-17', '22:46:00', 'No se presentó', 0, 0),
(24, 6, NULL, 1, '2025-11-17', '22:37:57', 'Completada', 0, 0),
(25, 6, 2, 1, '2025-11-17', '22:43:16', 'Completada', 0, 0),
(26, 6, NULL, 1, '2025-11-17', '22:43:44', 'Completada', 0, 0),
(27, 1, 2, 1, '2025-11-17', '23:50:20', 'Completada', 0, 1),
(28, 1, NULL, 1, '2025-11-18', '00:33:20', 'Completada', 1, 0),
(29, 1, 2, 1, '2025-11-18', '00:39:52', 'Completada', 1, 0),
(30, 1, 2, 1, '2025-11-18', '00:41:24', 'Completada', 0, 1),
(31, 1, NULL, 1, '2025-11-18', '00:42:47', 'Completada', 0, 1),
(32, 1, 2, 1, '2025-11-18', '01:09:11', 'Completada', 0, 1),
(33, 1, 2, 1, '2025-11-18', '21:00:13', 'Completada', 0, 1),
(34, 1, 2, 1, '2025-11-18', '21:00:54', 'Completada', 0, 1),
(35, 1, 2, 1, '2025-11-18', '21:40:02', 'Completada', 0, 0),
(36, 1, NULL, 1, '2025-11-18', '21:41:14', 'Completada', 0, 0),
(37, 1, NULL, 1, '2025-11-18', '21:43:37', 'Completada', 0, 0),
(38, 1, NULL, 1, '2025-11-18', '21:44:11', 'Completada', 0, 1),
(39, 1, NULL, 1, '2025-11-18', '21:50:47', 'Completada', 0, 1),
(40, 1, 2, 1, '2025-11-18', '21:51:30', 'Completada', 0, 1),
(41, 1, 2, 1, '2025-11-18', '21:58:02', 'Completada', 0, 1),
(42, 1, NULL, 1, '2025-11-18', '21:58:39', 'Completada', 0, 1),
(43, 1, NULL, 1, '2025-11-18', '22:01:19', 'Completada', 0, 1),
(44, 1, 2, 1, '2025-11-18', '22:01:58', 'Completada', 0, 1),
(45, 2, 2, 1, '2025-11-18', '22:02:43', 'Completada', 0, 1),
(46, 2, 2, 1, '2025-11-18', '22:05:40', 'Completada', 0, 1),
(47, 4, 2, 1, '2025-11-18', '23:08:18', 'Completada', 0, 1),
(48, 2, 2, 1, '2025-11-19', '13:56:00', 'Completada', 0, 1),
(49, 7, 2, 1, '2025-11-21', '14:16:00', 'Completada', 1, 1),
(50, 7, 2, 1, '2025-11-21', '14:22:00', 'Completada', 1, 1),
(51, 5, 2, 1, '2025-11-21', '14:24:00', 'Completada', 1, 1),
(52, 7, 2, 1, '2025-11-21', '03:11:14', 'Completada', 1, 1),
(53, 2, 2, 1, '2025-11-21', '15:34:00', 'Completada', 1, 1),
(54, 2, 2, 1, '2025-11-21', '15:58:00', 'Completada', 1, 1),
(55, 8, 2, 1, '2025-11-21', '22:20:00', 'Completada', 0, 1),
(56, 2, 2, 1, '2025-11-21', '23:19:00', 'Completada', 0, 0),
(57, 7, 2, 1, '2025-11-21', '22:30:38', 'Completada', 0, 1),
(59, 9, 2, 3, '2026-01-15', '12:57:00', 'Programada', 0, 0),
(60, 9, 2, 3, '2026-01-15', '12:58:00', 'Programada', 0, 0),
(61, 1, 2, 1, '2026-01-13', '02:00:32', 'Completada', 1, 0),
(62, 5, 2, 3, '2026-01-13', '14:27:00', 'Programada', 1, 0),
(63, 1, 2, 1, '2026-01-13', '13:39:00', 'Programada', 0, 0),
(64, 1, 2, 3, '2026-01-13', '14:39:00', 'Programada', 0, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras`
--

CREATE TABLE `compras` (
  `id` int(11) NOT NULL,
  `correlativo` varchar(20) NOT NULL,
  `id_proveedor` int(11) DEFAULT NULL,
  `numero_factura` varchar(100) DEFAULT NULL,
  `numero_orden` varchar(100) DEFAULT NULL,
  `fecha_compra` date NOT NULL,
  `total_compra` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estado` enum('Borrador','Recibida','Cancelada') NOT NULL DEFAULT 'Borrador',
  `id_usuario` int(11) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `compras`
--

INSERT INTO `compras` (`id`, `correlativo`, `id_proveedor`, `numero_factura`, `numero_orden`, `fecha_compra`, `total_compra`, `estado`, `id_usuario`, `fecha_creacion`) VALUES
(1, 'C-00001', 1, '000-02-001-012541', '', '2025-08-19', 200.00, 'Recibida', 1, '2025-08-19 05:32:48'),
(2, 'C-00002', 1, '000-02-001-012542', '', '2025-08-19', 100.00, 'Recibida', 1, '2025-08-19 05:54:29'),
(3, 'C-00003', 1, '22-21-541254', '2550', '2025-08-19', 90.00, 'Recibida', 1, '2025-08-19 06:36:34'),
(4, 'C-00004', 1, '001100110011', '255123', '2025-08-19', 1610.00, 'Recibida', 1, '2025-08-19 06:59:56'),
(5, 'C-00005', 1, '11', '112233', '2025-08-19', 575.00, 'Cancelada', 1, '2025-08-19 08:42:08'),
(6, 'C-00006', 1, '22-21-5412556', '25512-25', '2025-08-21', 172.50, 'Recibida', 1, '2025-08-21 07:11:03'),
(7, 'C-00007', 1, '000-02-001-012543', '25512-26', '2025-08-21', 1000.00, 'Recibida', 1, '2025-08-21 08:26:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras_detalle`
--

CREATE TABLE `compras_detalle` (
  `id` int(11) NOT NULL,
  `id_compra` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_compra` decimal(10,2) NOT NULL,
  `id_isv` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `compras_detalle`
--

INSERT INTO `compras_detalle` (`id`, `id_compra`, `id_producto`, `cantidad`, `precio_compra`, `id_isv`) VALUES
(1, 1, 1, 1, 100.00, 1),
(2, 1, 1, 1, 100.00, 1),
(3, 2, 1, 1, 100.00, 1),
(16, 3, 1, 1, 90.00, 1),
(21, 5, 1, 1, 500.00, 2),
(23, 4, 1, 10, 140.00, 2),
(24, 6, 1, 1, 150.00, 2),
(25, 7, 1, 10, 100.00, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id` int(11) NOT NULL DEFAULT 1,
  `nombre_clinica` varchar(100) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `rtn` varchar(50) DEFAULT NULL,
  `theme_mode` enum('light','dark') NOT NULL DEFAULT 'light' COMMENT 'Modo claro u oscuro',
  `background_color` varchar(7) NOT NULL DEFAULT '#f8f9fa' COMMENT 'Color de fondo para el modo claro',
  `navbar_color` varchar(7) NOT NULL DEFAULT '#343a40' COMMENT 'Color de la barra de navegación',
  `navbar_sticky` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0 = Normal, 1 = Fija (Sticky)',
  `mostrar_footer_bar` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = Sí, 0 = No',
  `zona_horaria` varchar(100) NOT NULL DEFAULT 'America/Tegucigalpa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id`, `nombre_clinica`, `logo`, `direccion`, `telefono`, `email`, `rtn`, `theme_mode`, `background_color`, `navbar_color`, `navbar_sticky`, `mostrar_footer_bar`, `zona_horaria`) VALUES
(1, 'LaserVision', 'logo.jpg', 'Calle Principal, Ciudad', '+504 2222-3333', 'info@clinicamvc.com', '0801-1990-123456-7', 'light', '#f2f2f2', '#4480bb', 1, 1, 'America/Tegucigalpa');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion_facturacion`
--

CREATE TABLE `configuracion_facturacion` (
  `id` int(11) NOT NULL DEFAULT 1,
  `prefijo_correlativo` varchar(10) NOT NULL DEFAULT 'FACT-',
  `siguiente_numero` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuracion_facturacion`
--

INSERT INTO `configuracion_facturacion` (`id`, `prefijo_correlativo`, `siguiente_numero`) VALUES
(1, 'F-', 153);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion_notas_credito`
--

CREATE TABLE `configuracion_notas_credito` (
  `id` int(11) NOT NULL DEFAULT 1,
  `prefijo_correlativo` varchar(10) NOT NULL DEFAULT 'NC-',
  `siguiente_numero` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuracion_notas_credito`
--

INSERT INTO `configuracion_notas_credito` (`id`, `prefijo_correlativo`, `siguiente_numero`) VALUES
(1, 'NC-', 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `consulta_plantillas`
--

CREATE TABLE `consulta_plantillas` (
  `id` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `contenido` text NOT NULL,
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `consulta_plantillas`
--

INSERT INTO `consulta_plantillas` (`id`, `titulo`, `contenido`, `estado`) VALUES
(1, 'Plantilla ejemplo', '<p>• Texto 1 para plantilla<br><br>• Texto 2 para plantilla</p>', 'Activo'),
(2, 'Plantilla ejemplo 2', '<p>• Contenido de plantilla <b>2</b></p><p><b>EDITADO</b></p>', 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cotizaciones`
--

CREATE TABLE `cotizaciones` (
  `id` int(11) NOT NULL,
  `correlativo` varchar(20) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `fecha_emision` date NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `isv_total` decimal(10,2) NOT NULL,
  `descuento_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `estado` enum('Borrador','Enviada','Aceptada','Rechazada','Facturada') NOT NULL DEFAULT 'Borrador',
  `notas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cotizaciones`
--

INSERT INTO `cotizaciones` (`id`, `correlativo`, `id_paciente`, `fecha_emision`, `fecha_vencimiento`, `subtotal`, `isv_total`, `descuento_total`, `total`, `id_usuario`, `estado`, `notas`) VALUES
(1, 'COT-00001', 1, '2025-08-26', '2025-09-09', 630.00, 19.50, 100.00, 549.50, 1, 'Facturada', ''),
(2, 'COT-00002', 1, '2025-08-28', '2025-09-11', 130.00, 0.00, 0.00, 130.00, 1, 'Borrador', ''),
(3, 'COT-00003', 2, '2025-08-27', '2025-09-11', 130.00, 0.00, 0.00, 130.00, 1, 'Borrador', ''),
(4, 'COT-00004', 3, '2025-08-27', '2025-09-11', 130.00, 0.00, 0.00, 130.00, 1, 'Borrador', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cotizacion_detalle`
--

CREATE TABLE `cotizacion_detalle` (
  `id` int(11) NOT NULL,
  `id_cotizacion` int(11) NOT NULL,
  `tipo_item` varchar(20) NOT NULL,
  `id_item` int(11) NOT NULL,
  `descripcion_item` varchar(255) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `descuento` decimal(10,2) NOT NULL DEFAULT 0.00,
  `id_isv` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cotizacion_detalle`
--

INSERT INTO `cotizacion_detalle` (`id`, `id_cotizacion`, `tipo_item`, `id_item`, `descripcion_item`, `cantidad`, `precio_unitario`, `descuento`, `id_isv`) VALUES
(4, 1, 'Producto', 1, 'Nombre del producto', 1, 130.00, 0.00, 2),
(5, 1, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1),
(6, 2, 'Producto', 1, 'Nombre del producto (Código: 001122)', 1, 130.00, 0.00, 1),
(7, 3, 'Producto', 1, 'Nombre del producto (Código: 001122)', 1, 130.00, 0.00, 1),
(8, 4, 'Producto', 1, 'Nombre del producto (Código: 001122)', 1, 130.00, 0.00, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dashboard_widgets`
--

CREATE TABLE `dashboard_widgets` (
  `id` int(11) NOT NULL,
  `widget_key` varchar(50) NOT NULL COMMENT 'Identificador único para el código',
  `titulo` varchar(100) NOT NULL COMMENT 'Título visible para el usuario',
  `descripcion` text DEFAULT NULL COMMENT 'Explicación de lo que muestra el widget',
  `activo` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = Visible, 0 = Oculto',
  `orden` int(11) NOT NULL DEFAULT 0,
  `rol_requerido` enum('Todos','Admin') NOT NULL DEFAULT 'Todos' COMMENT 'Qué rol puede ver el widget'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `dashboard_widgets`
--

INSERT INTO `dashboard_widgets` (`id`, `widget_key`, `titulo`, `descripcion`, `activo`, `orden`, `rol_requerido`) VALUES
(1, 'kpi_citas_hoy', 'Citas para Hoy', 'Muestra el número total de citas programadas para el día actual.', 1, 10, 'Todos'),
(2, 'kpi_facturacion_dia', 'Facturación del Día', 'Muestra la suma total de las facturas pagadas durante el día actual.', 1, 20, 'Todos'),
(3, 'kpi_pacientes_nuevos', 'Pacientes Nuevos (Mes)', 'Muestra cuántos pacientes nuevos se han registrado en el mes actual.', 1, 30, 'Todos'),
(4, 'kpi_cuentas_por_cobrar', 'Saldo en Cuentas por Cobrar', 'Muestra el monto total de todas las facturas con saldo pendiente.', 1, 40, 'Todos'),
(5, 'chart_ingresos_semana', 'Gráfico: Ingresos Últimos 7 Días', 'Un gráfico de barras que muestra los ingresos diarios de la última semana.', 1, 50, 'Todos'),
(6, 'list_proximas_citas', 'Lista: Próximas 5 Citas', 'Una tabla simple con las siguientes cinco citas programadas para el día.', 1, 60, 'Todos'),
(7, 'list_stock_bajo', 'Alerta: Productos con Bajo Stock', 'Una lista de los productos cuyo stock actual es igual or menor a su stock mínimo.', 1, 70, 'Todos'),
(8, 'chart_ingresos_mensuales', 'Ingresos Mensuales por Categoría', 'Gráfico de líneas comparando los ingresos mensuales por Productos y Servicios durante los últimos 12 meses.', 1, 80, 'Admin'),
(9, 'kpi_pio_alta', 'Alerta: PIO Alta (Hoy)', 'Pacientes atendidos hoy con Presión Intraocular > 21 mmHg.', 1, 45, 'Todos');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `diagnosticos`
--

CREATE TABLE `diagnosticos` (
  `id` int(11) NOT NULL,
  `codigo` varchar(20) DEFAULT NULL COMMENT 'Código (Ej. CIE-10 como H25.1)',
  `descripcion` varchar(255) NOT NULL COMMENT 'Descripción del diagnóstico',
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `diagnosticos`
--

INSERT INTO `diagnosticos` (`id`, `codigo`, `descripcion`, `estado`) VALUES
(1, 'H10-H13', 'Trastornos de la conjuntiva.', 'Activo'),
(2, 'H00-H06', 'Trastornos del párpado, aparato lagrimal y órbita.', 'Activo'),
(3, 'H15-H19', 'Trastornos de la esclerótica y la córnea.', 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `especialidades`
--

CREATE TABLE `especialidades` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `especialidades`
--

INSERT INTO `especialidades` (`id`, `nombre`, `estado`) VALUES
(1, 'Oftalmología', 'Activo'),
(2, 'Neurología', 'Activo'),
(3, 'Medicina General', 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas`
--

CREATE TABLE `facturas` (
  `id` int(11) NOT NULL,
  `correlativo` varchar(20) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `id_medico` int(11) DEFAULT NULL,
  `id_tecnico` int(11) DEFAULT NULL,
  `fecha_emision` datetime NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `isv_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `descuento_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estado` enum('Borrador','Pagada','Anulada','Pago Parcial') NOT NULL DEFAULT 'Borrador',
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `facturas`
--

INSERT INTO `facturas` (`id`, `correlativo`, `id_paciente`, `id_medico`, `id_tecnico`, `fecha_emision`, `subtotal`, `isv_total`, `descuento_total`, `total`, `estado`, `id_usuario`) VALUES
(11, 'FACT-00001', 1, NULL, NULL, '2025-08-22 00:00:00', 500.00, 0.00, 0.00, 500.00, 'Anulada', 1),
(12, 'FACT-00012', 1, NULL, NULL, '2025-08-22 00:00:00', 500.00, 0.00, 0.00, 500.00, 'Pagada', 1),
(13, 'FACT-00013', 1, NULL, NULL, '2025-08-22 00:00:00', 630.00, 0.00, 0.00, 630.00, 'Pagada', 1),
(14, 'FACT-00014', 1, NULL, NULL, '2025-08-22 00:00:00', 1280.00, 0.00, 0.00, 1280.00, 'Pagada', 1),
(15, 'FACT-00015', 1, NULL, NULL, '2025-08-22 00:00:00', 500.00, 75.00, 0.00, 575.00, 'Pagada', 1),
(16, 'FACT-00016', 1, NULL, NULL, '2025-08-23 00:00:00', 500.00, 0.00, 0.00, 500.00, 'Pagada', 1),
(17, 'FACT-00017', 1, NULL, NULL, '2025-08-23 00:00:00', 500.00, 0.00, 0.00, 500.00, 'Pagada', 1),
(18, 'FACT-00018', 1, NULL, NULL, '2025-08-23 00:00:00', 500.00, 0.00, 100.00, 400.00, 'Pagada', 1),
(19, 'FACT-00019', 1, NULL, NULL, '2025-08-23 00:00:00', 500.00, 0.00, 0.00, 500.00, 'Pagada', 1),
(20, 'FACT-00020', 1, NULL, NULL, '2025-08-23 00:00:00', 500.00, 0.00, 0.00, 500.00, 'Pagada', 1),
(21, 'F-00100', 1, NULL, NULL, '2025-08-23 00:00:00', 5000.00, 0.00, 0.00, 5000.00, 'Pagada', 1),
(22, 'F-00101', 1, NULL, NULL, '2025-08-23 00:00:00', 130.00, 19.50, 0.00, 149.50, 'Pagada', 1),
(23, 'F-00102', 1, NULL, NULL, '2025-08-24 00:00:00', 650.00, 97.50, 100.00, 647.50, 'Pagada', 1),
(24, 'F-00103', 1, NULL, NULL, '2025-08-25 00:00:00', 500.00, 0.00, 0.00, 500.00, 'Pagada', 1),
(25, 'F-00104', 1, NULL, NULL, '2025-08-25 00:00:00', 130.00, 0.00, 0.00, 130.00, 'Pagada', 1),
(26, 'F-00105', 1, NULL, NULL, '2025-08-25 00:00:00', 130.00, 0.00, 0.00, 130.00, 'Pagada', 1),
(27, 'F-00106', 1, NULL, NULL, '2025-08-25 00:00:00', 250.00, 0.00, 0.00, 250.00, 'Pagada', 1),
(28, 'F-00107', 1, NULL, NULL, '2025-08-26 00:00:00', 630.00, 19.50, 100.00, 649.50, 'Pagada', 1),
(29, 'F-00108', 1, NULL, NULL, '2025-08-26 00:00:00', 130.00, 0.00, 0.00, 130.00, 'Pagada', 1),
(30, 'F-00109', 1, NULL, NULL, '2025-08-26 00:00:00', 500.00, 0.00, 0.00, 500.00, 'Pagada', 1),
(31, 'F-00110', 1, NULL, NULL, '2025-08-28 00:00:00', 130.00, 0.00, 0.00, 130.00, 'Borrador', 1),
(32, 'F-00111', 1, NULL, NULL, '2025-08-28 00:00:00', 130.00, 0.00, 0.00, 130.00, 'Borrador', 1),
(33, 'F-00112', 1, NULL, NULL, '2025-08-28 00:00:00', 130.00, 0.00, 0.00, 130.00, 'Borrador', 1),
(34, 'F-00113', 1, NULL, NULL, '2025-08-27 00:00:00', 800.00, 0.00, 0.00, 800.00, 'Borrador', 1),
(35, 'F-00114', 2, NULL, NULL, '2025-11-16 00:00:00', 390.00, 0.00, 0.00, 390.00, 'Pagada', 1),
(36, 'F-00115', 1, 2, 4, '2025-11-18 00:00:00', 500.00, 0.00, 0.00, 500.00, 'Borrador', 1),
(37, 'F-00116', 1, NULL, NULL, '2025-11-18 00:00:00', 5000.00, 0.00, 0.00, 5000.00, 'Borrador', 1),
(38, 'F-00117', 1, NULL, NULL, '2025-11-18 00:00:00', 500.00, 0.00, 0.00, 500.00, 'Borrador', 1),
(39, 'F-00118', 1, NULL, NULL, '2025-11-18 00:00:00', 5260.00, 0.00, 0.00, 5260.00, 'Pagada', 1),
(40, 'F-00119', 1, NULL, NULL, '2025-11-18 00:00:00', 650.00, 0.00, 0.00, 650.00, 'Pagada', 1),
(41, 'F-00120', 2, NULL, NULL, '2025-11-18 00:00:00', 910.00, 0.00, 0.00, 910.00, 'Pagada', 1),
(42, 'F-00121', 1, NULL, NULL, '2025-11-18 00:00:00', 130.00, 0.00, 0.00, 130.00, 'Pagada', 1),
(43, 'F-00122', 1, NULL, NULL, '2025-11-18 00:00:00', 500.00, 0.00, 0.00, 500.00, 'Borrador', 1),
(44, 'F-00123', 1, NULL, NULL, '2025-11-18 00:00:00', 130.00, 0.00, 0.00, 130.00, 'Pagada', 1),
(45, 'F-00124', 2, NULL, NULL, '2025-11-18 00:00:00', 650.00, 0.00, 0.00, 650.00, 'Pagada', 1),
(47, 'F-00125', 1, 1, NULL, '2025-11-18 00:00:00', 5000.00, 0.00, 0.00, 5000.00, 'Borrador', 1),
(48, 'F-00126', 1, 1, NULL, '2025-11-18 00:00:00', 5000.00, 0.00, 0.00, 5000.00, 'Borrador', 1),
(49, 'F-00127', 1, 2, NULL, '2025-11-18 00:00:00', 5000.00, 0.00, 0.00, 5000.00, 'Borrador', 1),
(50, 'F-00128', 1, 2, NULL, '2025-11-18 00:00:00', 5000.00, 0.00, 0.00, 5000.00, 'Borrador', 1),
(51, 'F-00129', 1, 1, NULL, '2025-11-18 00:00:00', 500.00, 0.00, 0.00, 500.00, 'Borrador', 1),
(52, 'F-00130', 1, 1, NULL, '2025-11-18 00:00:00', 5000.00, 0.00, 0.00, 5000.00, 'Pagada', 1),
(53, 'F-00131', 1, NULL, NULL, '2025-11-18 00:00:00', 500.00, 0.00, 0.00, 500.00, 'Pagada', 1),
(54, 'F-00132', 2, 2, NULL, '2025-11-18 00:00:00', 5000.00, 0.00, 0.00, 5000.00, 'Pagada', 1),
(55, 'F-00133', 2, 2, 4, '2025-11-18 00:00:00', 5000.00, 0.00, 0.00, 5000.00, 'Pagada', 1),
(56, 'F-00134', 5, 2, NULL, '2025-11-18 00:00:00', 130.00, 0.00, 0.00, 130.00, 'Pagada', 1),
(57, 'F-00135', 2, 2, 4, '2025-11-18 00:00:00', 500.00, 0.00, 0.00, 500.00, 'Pagada', 1),
(58, 'F-00136', 4, 2, NULL, '2025-11-18 00:00:00', 5000.00, 0.00, 0.00, 5000.00, 'Pagada', 1),
(59, 'F-00137', 2, 1, 4, '2025-11-19 08:36:55', 130.00, 0.00, 0.00, 130.00, 'Pagada', 1),
(60, 'F-00138', 2, 2, NULL, '2025-11-19 01:58:35', 500.00, 0.00, 0.00, 500.00, 'Pago Parcial', 1),
(61, 'F-00139', 5, 2, 4, '2025-11-21 02:13:03', 130.00, 0.00, 0.00, 130.00, 'Pagada', 1),
(62, 'F-00140', 7, 2, NULL, '2025-11-21 02:19:22', 130.00, 0.00, 0.00, 130.00, 'Borrador', 1),
(63, 'F-00141', 5, 2, 4, '2025-11-21 02:32:18', 130.00, 0.00, 0.00, 130.00, 'Borrador', 1),
(64, 'F-00142', 1, 2, NULL, '2025-11-18 22:01:58', 500.00, 0.00, 0.00, 500.00, 'Borrador', 1),
(65, 'F-00143', 1, 2, NULL, '2025-11-18 22:01:58', 630.00, 0.00, 0.00, 630.00, 'Borrador', 1),
(66, 'F-00144', 7, 2, NULL, '2025-11-21 03:11:14', 5130.00, 0.00, 0.00, 5130.00, 'Borrador', 3),
(67, 'F-00145', 7, 2, NULL, '2025-11-21 03:11:14', 5930.00, 0.00, 0.00, 5930.00, 'Borrador', 1),
(68, 'F-00146', 7, 2, NULL, '2025-11-21 03:11:14', 6060.00, 0.00, 0.00, 6060.00, 'Borrador', 1),
(69, 'F-00147', 7, 2, NULL, '2025-11-21 03:11:14', 6060.00, 0.00, 0.00, 6060.00, 'Anulada', 1),
(70, 'F-00148', 2, 2, NULL, '2025-11-21 15:34:00', 260.00, 0.00, 0.00, 260.00, 'Borrador', 3),
(71, 'F-00149', 7, 2, 4, '2025-11-21 21:19:38', 500.00, 0.00, 0.00, 500.00, 'Borrador', 1),
(72, 'F-00150', 8, 2, 4, '2025-11-21 21:22:45', 500.00, 0.00, 0.00, 500.00, 'Borrador', 1),
(73, 'F-00151', 2, 2, NULL, '2025-11-21 15:58:00', 5000.00, 0.00, 0.00, 5000.00, 'Borrador', 3),
(74, 'F-00152', 7, 2, NULL, '2025-11-21 22:30:38', 500.00, 0.00, 0.00, 500.00, 'Pago Parcial', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `factura_detalle`
--

CREATE TABLE `factura_detalle` (
  `id` int(11) NOT NULL,
  `id_factura` int(11) NOT NULL,
  `tipo_item` enum('Producto','Servicio') NOT NULL,
  `id_item` int(11) NOT NULL,
  `descripcion_item` varchar(255) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `descuento` decimal(10,2) NOT NULL DEFAULT 0.00,
  `id_isv` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `factura_detalle`
--

INSERT INTO `factura_detalle` (`id`, `id_factura`, `tipo_item`, `id_item`, `descripcion_item`, `cantidad`, `precio_unitario`, `descuento`, `id_isv`) VALUES
(1, 11, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1),
(2, 12, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1),
(5, 14, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1),
(6, 14, 'Producto', 1, 'Nombre del producto', 1, 130.00, 0.00, 1),
(7, 14, 'Producto', 1, 'Nombre del producto', 5, 130.00, 0.00, 1),
(10, 13, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1),
(11, 13, 'Producto', 1, 'Nombre del producto', 1, 130.00, 0.00, 1),
(13, 16, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1),
(14, 17, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1),
(17, 20, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1),
(18, 21, 'Servicio', 3, 'Nombre de la cirugía', 1, 5000.00, 0.00, 1),
(19, 15, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 2),
(20, 22, 'Producto', 1, 'Nombre del producto', 1, 130.00, 0.00, 2),
(22, 18, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1),
(42, 23, 'Producto', 1, 'Nombre del producto', 5, 130.00, 0.00, 2),
(44, 24, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1),
(46, 25, 'Producto', 1, 'Nombre del producto', 1, 130.00, 0.00, 1),
(47, 26, 'Producto', 1, 'Nombre del producto', 1, 130.00, 0.00, 1),
(48, 19, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1),
(49, 27, 'Producto', 1, 'Nombre del producto', 2, 130.00, 10.00, 1),
(50, 28, 'Producto', 1, 'Nombre del producto', 1, 130.00, 0.00, 2),
(51, 28, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1),
(52, 29, 'Producto', 1, 'Nombre del producto', 1, 130.00, 0.00, 1),
(53, 30, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1),
(54, 31, 'Producto', 1, 'Nombre del producto', 1, 130.00, 0.00, 1),
(55, 32, 'Producto', 1, 'Nombre del producto', 1, 130.00, 0.00, 1),
(56, 33, 'Producto', 1, 'Nombre del producto (Código: 001122)', 1, 130.00, 0.00, 1),
(57, 34, 'Servicio', 2, 'Sutura de Herida', 1, 800.00, 0.00, 1),
(58, 35, 'Producto', 1, 'Nombre del producto (Código: 001122)', 3, 130.00, 0.00, 1),
(60, 37, 'Servicio', 3, 'Nombre de la cirugía', 1, 5000.00, 0.00, 1),
(61, 38, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1),
(68, 41, 'Producto', 1, 'Nombre del producto (Código: 001122)', 7, 130.00, 0.00, 1),
(69, 40, 'Producto', 1, 'Nombre del producto (Código: 001122)', 5, 130.00, 0.00, 1),
(70, 39, 'Servicio', 3, 'Nombre de la cirugía', 1, 5000.00, 0.00, 1),
(71, 39, 'Producto', 1, 'Nombre del producto (Código: 001122)', 2, 130.00, 0.00, 1),
(72, 42, 'Producto', 1, 'Nombre del producto (Código: 001122)', 1, 130.00, 0.00, 1),
(73, 43, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1),
(74, 44, 'Producto', 1, 'Nombre del producto', 1, 130.00, 0.00, 1),
(75, 45, 'Producto', 1, 'Nombre del producto (Código: 001122)', 5, 130.00, 0.00, 1),
(76, 47, 'Servicio', 3, 'Nombre de la cirugía', 1, 5000.00, 0.00, 1),
(77, 48, 'Servicio', 3, 'Nombre de la cirugía', 1, 5000.00, 0.00, 1),
(78, 49, 'Servicio', 3, 'Nombre de la cirugía', 1, 5000.00, 0.00, 1),
(79, 50, 'Servicio', 3, 'Nombre de la cirugía', 1, 5000.00, 0.00, 1),
(80, 51, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1),
(82, 52, 'Servicio', 3, 'Nombre de la cirugía', 1, 5000.00, 0.00, 1),
(84, 54, 'Servicio', 3, 'Nombre de la cirugía', 1, 5000.00, 0.00, 1),
(86, 53, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1),
(87, 56, 'Producto', 1, 'Nombre del producto (Código: 001122)', 1, 130.00, 0.00, 1),
(93, 36, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1),
(94, 57, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1),
(95, 55, 'Servicio', 3, 'Nombre de la cirugía', 1, 5000.00, 0.00, 1),
(96, 58, 'Servicio', 3, 'Nombre de la cirugía', 1, 5000.00, 0.00, 1),
(97, 59, 'Producto', 1, 'Nombre del producto (Código: 001122)', 1, 130.00, 0.00, 1),
(98, 60, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1),
(99, 61, 'Producto', 1, 'Nombre del producto (Código: 001122)', 1, 130.00, 0.00, 1),
(101, 62, 'Producto', 1, 'Nombre del producto', 1, 130.00, 0.00, 1),
(103, 63, 'Producto', 1, 'Nombre del producto', 1, 130.00, 0.00, 1),
(104, 64, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1),
(105, 65, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1),
(106, 65, 'Producto', 1, 'Nombre del producto', 1, 130.00, 0.00, 1),
(107, 66, 'Producto', 1, 'Nombre del producto', 1, 130.00, 0.00, 1),
(108, 66, 'Servicio', 3, 'Nombre de la cirugía', 1, 5000.00, 0.00, 1),
(109, 67, 'Producto', 1, 'Nombre del producto', 1, 130.00, 0.00, 1),
(110, 67, 'Servicio', 3, 'Nombre de la cirugía', 1, 5000.00, 0.00, 1),
(111, 67, 'Servicio', 2, 'Sutura de Herida', 1, 800.00, 0.00, 1),
(112, 68, 'Producto', 1, 'Nombre del producto', 2, 130.00, 0.00, 1),
(113, 68, 'Servicio', 3, 'Nombre de la cirugía', 1, 5000.00, 0.00, 1),
(114, 68, 'Servicio', 2, 'Sutura de Herida', 1, 800.00, 0.00, 1),
(115, 69, 'Producto', 1, 'Nombre del producto', 2, 130.00, 0.00, 1),
(116, 69, 'Servicio', 3, 'Nombre de la cirugía', 1, 5000.00, 0.00, 1),
(117, 69, 'Servicio', 2, 'Sutura de Herida', 1, 800.00, 0.00, 1),
(127, 70, 'Producto', 1, 'Nombre del producto', 2, 130.00, 0.00, 1),
(128, 71, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1),
(130, 72, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1),
(131, 73, 'Servicio', 3, 'Nombre de la cirugía', 1, 5000.00, 0.00, 1),
(132, 74, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupos`
--

CREATE TABLE `grupos` (
  `id` int(11) NOT NULL,
  `nombre_grupo` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `grupos`
--

INSERT INTO `grupos` (`id`, `nombre_grupo`) VALUES
(1, 'Administradores'),
(3, 'Médicos'),
(2, 'Recepcionistas'),
(4, 'Técnicos');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_clinico`
--

CREATE TABLE `historial_clinico` (
  `id` int(11) NOT NULL,
  `id_cita` int(11) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `id_medico` int(11) NOT NULL,
  `hea` text DEFAULT NULL COMMENT 'Historia de la Enfermedad Actual',
  `av_sc_od` varchar(20) DEFAULT NULL COMMENT 'Agudeza Visual Sin Corrección OD',
  `av_sc_os` varchar(20) DEFAULT NULL COMMENT 'Agudeza Visual Sin Corrección OS',
  `av_cc_od` varchar(20) DEFAULT NULL COMMENT 'Agudeza Visual Con Corrección OD',
  `av_cc_os` varchar(20) DEFAULT NULL COMMENT 'Agudeza Visual Con Corrección OS',
  `pio_od` varchar(20) DEFAULT NULL COMMENT 'Presión Intraocular OD',
  `pio_os` varchar(20) DEFAULT NULL COMMENT 'Presión Intraocular OS',
  `biomicroscopia` text DEFAULT NULL COMMENT 'Hallazgos de Segmento Anterior',
  `fondo_ojo` text DEFAULT NULL COMMENT 'Hallazgos de Segmento Posterior',
  `diagnostico` text NOT NULL,
  `tratamiento` text NOT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `historial_clinico`
--

INSERT INTO `historial_clinico` (`id`, `id_cita`, `id_paciente`, `id_medico`, `hea`, `av_sc_od`, `av_sc_os`, `av_cc_od`, `av_cc_os`, `pio_od`, `pio_os`, `biomicroscopia`, `fondo_ojo`, `diagnostico`, `tratamiento`, `observaciones`, `fecha_registro`) VALUES
(1, 1, 1, 1, '', '', '', '', '', '', '', '', '', '<p><br></p><p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', '<p><br></p>', '<p><br></p>', '2025-08-07 05:14:30'),
(2, 2, 1, 1, '', '', '', '', '', '', '', '', '', 'Texto 1', 'Texto 2', 'Texto 3.1', '2025-08-07 06:32:47'),
(3, 3, 1, 1, '', '', '', '', '', '', '', '', '', '<p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', '<p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', '<p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', '2025-08-11 07:23:41'),
(4, 4, 1, 2, '', '', '', '', '', '', '', '', '', '<p><br></p><p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', '<p><br></p><p><b>Otro plan:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', '<p><br></p>', '2025-08-12 09:17:18'),
(5, 5, 1, 2, '', '', '', '', '', '', '', '', '', '<p><br></p><p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', '<p>2</p><p>4</p><p>5</p>', '<p><br></p>', '2025-08-13 07:07:15'),
(6, 6, 1, 1, '', '', '', '', '', '', '', '', '', '<p><br></p><p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', '<p><br></p><p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', '<p><br></p>', '2025-08-25 09:36:06'),
(7, 7, 1, 2, '', '', '', '', '', '', '', '', '', '<p>1</p>', '<p>2</p>', 'Null', '2025-08-26 07:21:40'),
(8, 8, 1, 2, 'HEAZ', '20/40', '20/30', '20/20', '20/20', '15', '16', 'Biomicroscopía', 'Fondo de Ojo', '<p><br></p>', '<p>Plan aquí.</p>', 'Observaciones', '2025-08-26 07:29:30'),
(9, 11, 3, 2, '', '', '', '', '', '', '', '', '', '<p>• Texto 1 para plantilla<br><br>• Texto 2 para plantilla</p>', '<p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', '', '2025-11-14 05:10:52'),
(10, 10, 3, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '<p>• Texto 1 para plantilla<br><br>• Texto 2 para plantilla</p><p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', '<p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.X</li></ul>', '<p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', '2025-11-15 09:15:47'),
(11, 12, 4, 2, '', '', '', '', '', '', '', '<p><br></p>', '<p><br></p>', '', '<p>Plan para H15</p>', '', '2025-11-16 11:49:22'),
(14, 24, 6, 1, '', '', '', '', '', '', '', '<p><br></p>', '<p><br></p>', '', '<p>PLAN AQUÍ</p>', '', '2025-11-18 04:38:10'),
(15, 25, 6, 2, '', '', '', '', '', '', '', '<p><br></p>', '<p><br></p>', '', '<p>PLAN X01</p>', '', '2025-11-18 04:43:25'),
(16, 26, 6, 1, '', '', '', '', '', '', '', '<p><br></p>', '<p><br></p>', '', '<p>PLAN x02</p>', '', '2025-11-18 04:43:52'),
(17, 15, 1, 2, 'HEA2', '', '', '', '', '', '', '<p><br></p>', '<p><br></p>', '', '<p>PLAN</p>', '', '2025-11-18 05:47:13'),
(18, 27, 1, 2, '', '', '', '', '', '', '', '<p><br></p>', '<p><br></p>', '', '<p>PLAN3</p>', '', '2025-11-18 05:50:32'),
(19, 28, 1, 1, '', '', '', '', '', '', '', '<p><br></p>', '<p><br></p>', '', '<p>PLAN 45</p>', '', '2025-11-18 06:33:32'),
(20, 29, 1, 2, '', '', '', '', '', '', '', '<p><br></p>', '<p><br></p>', '', '<p>PLAN 46</p>', '', '2025-11-18 06:40:05'),
(21, 30, 1, 2, '', '', '', '', '', '', '', '<p><br></p>', '<p><br></p>', '', '<p>PLAN 10</p>', '', '2025-11-18 06:41:39'),
(22, 31, 1, 1, '', '', '', '', '', '', '', '<p><br></p>', '<p><br></p>', '', '<p>Sin Plan</p>', '', '2025-11-18 06:43:05'),
(23, 32, 1, 2, 'sdfasdfas', '', '', '', '', '', '', '<p><br></p>', '<p><br></p>', '', '<p>asdfafffs</p>', '', '2025-11-18 07:09:25'),
(24, 33, 1, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '<p><br></p>', '<p><br></p>', '', '<p>PLAN 46</p>', NULL, '2025-11-19 03:00:26'),
(25, 34, 1, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '<p><br></p>', '<p><br></p>', '', '<p>Plan qui 22</p>', NULL, '2025-11-19 03:01:13'),
(26, 35, 1, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '<p><br></p>', '<p><br></p>', '', '<p>E plan aqui 222</p>', NULL, '2025-11-19 03:40:13'),
(28, 37, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '<p><br></p>', '<p><br></p>', '', '<p>fasdgfas</p>', NULL, '2025-11-19 03:43:43'),
(29, 38, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '<p><br></p>', '<p><br></p>', '', '<p>afasfas</p>', NULL, '2025-11-19 03:44:22'),
(30, 39, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '<p><br></p>', '<p><br></p>', '', '<p>sdfgsdfg</p>', NULL, '2025-11-19 03:50:58'),
(31, 40, 1, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '<p><br></p>', '<p><br></p>', '', '<p>fgagfsdasdfg</p>', NULL, '2025-11-19 03:51:46'),
(32, 41, 1, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '<p><br></p>', '<p><br></p>', '', '<p>gasdfgasdfg</p>', NULL, '2025-11-19 03:58:13'),
(33, 42, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '<p><br></p>', '<p><br></p>', '', '<p>aggweg</p>', NULL, '2025-11-19 03:58:50'),
(34, 43, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '<p><br></p>', '<p><br></p>', '', '<p>asdasda</p>', NULL, '2025-11-19 04:01:30'),
(35, 44, 1, 2, '<p>• Contenido de plantilla <b>2</b></p><p><b>EDITADO</b></p>', NULL, NULL, NULL, NULL, NULL, NULL, '<p><br></p>', '<p><br></p>', '', '<p>00123</p>', NULL, '2025-11-19 04:02:11'),
(36, 45, 2, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '<p><br></p>', '<p><br></p>', '', '<p>wdqqwdqwd</p>', NULL, '2025-11-19 04:02:57'),
(37, 46, 2, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '<p><br></p>', '<p><br></p>', '', '<p>123456</p>', NULL, '2025-11-19 04:05:50'),
(38, 47, 4, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '<p><br></p>', '<p><br></p>', '', '<p>xcvnxnxgnx</p>', NULL, '2025-11-19 05:08:31'),
(39, 48, 2, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '<p><br></p>', '<p><br></p>', '', '<p>Plan 444</p>', NULL, '2025-11-19 07:58:35'),
(40, 49, 7, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '<p><br></p>', '<p><br></p>', '', '<p>Plan para Carlos</p>', NULL, '2025-11-21 08:16:59'),
(41, 50, 7, 2, 'HEA Aquí', NULL, NULL, NULL, NULL, NULL, NULL, '<p><br></p>', '<p><br></p>', '', '<p>Plan 2 para Carlos</p>', NULL, '2025-11-21 08:23:29'),
(42, 51, 5, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '<p><br></p>', '<p><br></p>', '', '<p>Plano</p>', NULL, '2025-11-21 08:25:06'),
(43, 52, 7, 2, 'HEA2 Aquí35', NULL, NULL, NULL, NULL, NULL, NULL, '<p><br></p>', '<p><br></p>', '', '<p>Plan 2 para Carlos</p>', NULL, '2025-11-21 09:11:19'),
(44, 53, 2, 2, 'HEA Andres Galindo 2', NULL, NULL, NULL, NULL, NULL, NULL, '<p><br></p>', '<p><br></p>', '', '<p>Tratamiento aquí 2</p>', NULL, '2025-11-21 09:41:49'),
(45, 55, 8, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '<p><br></p>', '<p><br></p>', '', '<p>Diagnóstico aquí</p>', NULL, '2025-11-22 03:21:59'),
(46, 54, 2, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '<p><br></p>', '<p><br></p>', '', '<p><br></p>', NULL, '2025-11-22 04:00:59'),
(47, 56, 2, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '<p><br></p>', '<p><br></p>', '', '<p>XxXxXxXxZzZzZz</p>', NULL, '2025-11-22 04:20:22'),
(48, 57, 7, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '<p><br></p>', '<p><br></p>', '', '<p>dsfhshsdfhdf</p>', NULL, '2025-11-22 04:31:00'),
(49, 61, 1, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '<p><br></p>', '<p><br></p>', '', '<p><br></p><p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', NULL, '2026-01-13 08:00:43');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_clinico_log`
--

CREATE TABLE `historial_clinico_log` (
  `id` int(11) NOT NULL,
  `id_historial` int(11) NOT NULL COMMENT 'Referencia a la entrada original en historial_clinico',
  `id_usuario_modifica` int(11) NOT NULL COMMENT 'Usuario que realizó el cambio',
  `fecha_modificacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `diagnostico_anterior` text DEFAULT NULL,
  `tratamiento_anterior` text DEFAULT NULL,
  `observaciones_anterior` text DEFAULT NULL,
  `accion` varchar(50) NOT NULL DEFAULT 'UPDATE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registra los cambios en las entradas del historial clínico';

--
-- Volcado de datos para la tabla `historial_clinico_log`
--

INSERT INTO `historial_clinico_log` (`id`, `id_historial`, `id_usuario_modifica`, `fecha_modificacion`, `diagnostico_anterior`, `tratamiento_anterior`, `observaciones_anterior`, `accion`) VALUES
(1, 8, 1, '2025-08-28 08:19:33', '<p>1</p>', '<p>2</p>', '<p><br></p>', 'UPDATE'),
(2, 8, 1, '2025-08-28 08:20:00', '<p>1</p>', '<p>2</p>', '<p>3</p>', 'UPDATE'),
(3, 8, 1, '2025-08-29 05:55:35', '<p>1</p>', '<p>2</p>', '<p>4</p>', 'UPDATE'),
(4, 8, 1, '2025-08-29 07:10:45', '<p>1</p>', '<p>2</p>', '<p>4</p>', 'UPDATE'),
(5, 8, 1, '2025-08-29 07:21:57', '<p>1</p><p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', '<p>2</p><p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', '<p>4</p>', 'UPDATE'),
(6, 8, 1, '2025-08-29 07:31:12', '<p>1</p><p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', '<p>2</p><p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', '<p>4</p>', 'UPDATE'),
(7, 8, 1, '2025-08-29 07:31:23', '<p>1</p><p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', '<p>2</p><p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', '<p>4</p>', 'UPDATE'),
(8, 8, 1, '2025-11-14 04:40:23', '<p>• Texto 1 para plantilla<br><br>• Texto 2 para plantilla</p>', '<p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', '', 'UPDATE'),
(9, 9, 3, '2025-11-15 09:10:47', '<p>• Texto 1 para plantilla<br><br>• Texto 2 para plantilla</p>', '<p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', '', 'UPDATE'),
(10, 10, 1, '2025-11-15 09:24:51', '<p>• Texto 1 para plantilla<br><br>• Texto 2 para plantilla</p>', '<p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', '', 'UPDATE'),
(11, 10, 1, '2025-11-15 09:25:50', '<p>• Texto 1 para plantilla<br><br>• Texto 2 para plantilla</p>', '<p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', '', 'UPDATE'),
(12, 10, 1, '2025-11-15 09:35:20', '<p>• Texto 1 para plantilla<br><br>• Texto 2 para plantilla</p>', '<p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', '', 'UPDATE'),
(13, 10, 1, '2025-11-15 09:48:59', '<p>• Texto 1 para plantilla<br><br>• Texto 2 para plantilla</p>', '<p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', '', 'UPDATE'),
(14, 10, 1, '2025-11-15 09:54:29', '<p>• Texto 1 para plantilla<br><br>• Texto 2 para plantilla</p>', '<p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', '', 'UPDATE'),
(15, 10, 1, '2025-11-15 09:57:42', '<p>• Texto 1 para plantilla<br><br>• Texto 2 para plantilla</p>', '<p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', '', 'UPDATE'),
(16, 10, 1, '2025-11-15 10:16:45', '<p>• Texto 1 para plantilla<br><br>• Texto 2 para plantilla</p>', '<p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', '', 'UPDATE'),
(17, 10, 1, '2025-11-15 10:31:56', '<p>• Texto 1 para plantilla<br><br>• Texto 2 para plantilla</p>', '<p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.X</li></ul>', '', 'UPDATE'),
(18, 10, 1, '2025-11-15 10:32:31', '<p>• Texto 1 para plantilla<br><br>• Texto 2 para plantilla</p>', '<p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.X</li></ul>', '<p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', 'UPDATE'),
(19, 8, 1, '2025-11-15 11:15:03', '<p>• Texto 1 para plantilla<br><br>• Texto 2 para plantilla</p><p>• Texto 3 para plantilla</p>', '<p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', '', 'UPDATE'),
(20, 8, 1, '2025-11-15 11:19:21', '<p>• Texto 1 para plantilla<br><br>• Texto 2 para plantilla</p>', '<p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', '', 'UPDATE'),
(21, 8, 1, '2025-11-15 11:20:59', '<p>• Texto 1 para plantilla<br><br>• Texto 2 para plantilla</p>', '<p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', '', 'UPDATE'),
(22, 8, 1, '2025-11-15 11:21:06', '<p>• Texto 1 para plantilla<br><br>• Texto 2 para plantilla</p>', '<p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', '', 'UPDATE'),
(23, 8, 1, '2025-11-15 11:56:36', '<p>• Texto 1 para plantilla<br><br>• Texto 2 para plantilla</p>', '<p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', '', 'UPDATE'),
(24, 8, 1, '2025-11-15 11:56:51', '<p>• Texto 1 para plantilla<br><br>• Texto 2 para plantilla</p>', '<p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', '', 'UPDATE'),
(25, 8, 1, '2025-11-15 11:57:44', '<p>• Texto 1 para plantilla<br><br>• Texto 2 para plantilla</p>', '<p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', '', 'UPDATE'),
(26, 8, 1, '2025-11-15 12:01:17', '<p>• Texto 1 para plantilla<br></p>', '<p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', '', 'UPDATE'),
(27, 7, 1, '2025-11-15 12:02:46', '<p>1</p>', '<p>2</p>', '<p><br></p>', 'UPDATE'),
(28, 8, 1, '2025-11-16 04:50:08', '<p>• Texto 1 para plantilla<br></p>', '<p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', '', 'UPDATE'),
(29, 8, 1, '2025-11-16 04:54:13', '<p>• Texto 1 para plantilla<br></p>', '<p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', '', 'UPDATE'),
(30, 8, 1, '2025-11-16 04:54:59', '<p><br></p>', '<p><br></p>', '', 'UPDATE'),
(31, 8, 1, '2025-11-16 04:56:12', '<p><br></p>', '<p><br></p>', 'Observaciones Generales del Examen', 'UPDATE'),
(32, 8, 1, '2025-11-16 04:56:45', '<p><br></p>', '<p><br></p>', 'Observaciones Generales del Examen', 'UPDATE'),
(33, 8, 1, '2025-11-16 05:11:39', '<p><br></p>', '<p><br></p>', 'Observaciones Generales del Examen', 'UPDATE'),
(34, 8, 1, '2025-11-16 06:23:41', '<p><br></p>', '<p><br></p>', 'Observaciones Generales del Examen', 'UPDATE'),
(35, 8, 1, '2025-11-16 06:23:58', '<p><br></p>', '<p><br></p>', '', 'UPDATE'),
(36, 8, 1, '2025-11-16 06:24:11', '<p><br></p>', '<p><br></p>', 'Observaciones Generales del Examen', 'UPDATE'),
(37, 8, 1, '2025-11-16 07:17:21', '', '<p><br></p>', '', 'UPDATE'),
(38, 8, 1, '2025-11-16 07:17:27', '', '<p><br></p>', '', 'UPDATE'),
(39, 8, 1, '2025-11-16 07:18:20', '', '<p><br></p>', '', 'UPDATE'),
(40, 8, 1, '2025-11-16 07:19:13', '', '<p>Plan aquí</p>', '', 'UPDATE'),
(41, 8, 1, '2025-11-16 08:11:42', '', '<p>Plan aquí</p>', 'Observaciones', 'UPDATE'),
(42, 8, 1, '2025-11-16 09:30:26', '', '<p>Plan aquí</p>', 'Observaciones', 'UPDATE'),
(43, 8, 1, '2025-11-16 09:31:53', '', '<p>Plan aquí</p>', 'Observaciones', 'UPDATE'),
(44, 8, 1, '2025-11-16 10:05:09', '', '<p>Plan aquí</p>', 'Observaciones', 'UPDATE'),
(45, 8, 1, '2025-11-16 10:53:16', '', '<p>Plan aquí</p>', 'Observaciones', 'UPDATE'),
(46, 10, 1, '2025-11-16 11:46:02', '', '<p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.X</li></ul>', '<p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', 'UPDATE'),
(47, 7, 1, '2025-11-16 12:34:46', '', '<p>2</p>', '<p><br></p>', 'UPDATE'),
(48, 7, 1, '2025-11-16 12:35:36', '', '<p>2</p>', '<p><br></p>', 'UPDATE'),
(49, 8, 1, '2025-11-17 09:07:17', '', '<p>Plan aquí.</p>', 'Observaciones', 'UPDATE'),
(50, 8, 1, '2025-11-17 09:18:42', '', '<p>Plan aquí.</p>', 'Observaciones', 'UPDATE'),
(51, 8, 1, '2025-11-17 09:28:54', '', '<p>Plan aquí.</p>', 'Observaciones', 'UPDATE'),
(52, 8, 1, '2025-11-18 04:15:18', '', '<p>Plan aquí.</p>', 'Observaciones', 'UPDATE'),
(54, 8, 1, '2025-11-18 04:35:06', '', '<p>Plan aquí.</p>', 'Observaciones', 'UPDATE'),
(55, 8, 1, '2025-11-18 05:02:03', '', '<p>Plan aquí.</p>', 'Observaciones', 'UPDATE'),
(56, 8, 1, '2025-11-18 05:29:58', '', '<p>Plan aquí.</p>', 'Observaciones', 'UPDATE'),
(57, 8, 1, '2025-11-18 05:35:05', '', '<p>Plan aquí.</p>', 'Observaciones', 'UPDATE'),
(58, 8, 1, '2025-11-18 05:35:32', '', '<p>Plan aquí.</p>', 'Observaciones', 'UPDATE'),
(59, 11, 1, '2025-11-18 05:43:22', '', '<p>Plan para H15</p>', '', 'UPDATE'),
(60, 8, 1, '2025-11-18 05:44:05', '', '<p>Plan aquí.</p>', 'Observaciones', 'UPDATE'),
(61, 17, 1, '2025-11-18 05:48:56', '', '<p>PLAN</p>', '', 'UPDATE'),
(62, 17, 1, '2025-11-18 05:49:35', '', '<p>PLAN</p>', '', 'UPDATE'),
(63, 17, 1, '2025-11-18 05:49:43', '', '<p>PLAN</p>', '', 'UPDATE'),
(64, 17, 1, '2025-11-18 05:49:55', '', '<p>PLAN</p>', '', 'UPDATE'),
(65, 19, 1, '2025-11-18 06:33:46', '', '<p>PLAN 4</p>', '', 'UPDATE'),
(66, 19, 1, '2025-11-18 06:39:43', '', '<p>PLAN 4</p>', '', 'UPDATE'),
(67, 20, 1, '2025-11-18 06:40:23', '', '<p>PLAN 46</p>', '', 'UPDATE'),
(68, 22, 1, '2025-11-18 07:08:39', '', '<p>Sin Plan</p>', '', 'UPDATE'),
(69, 26, 1, '2025-11-19 03:40:23', NULL, '<p>E plan aqui 222</p>', NULL, 'UPDATE'),
(70, 28, 1, '2025-11-19 03:43:56', NULL, '<p>fasdgfas</p>', NULL, 'UPDATE'),
(71, 10, 1, '2025-11-19 04:04:20', NULL, '<p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.X</li></ul>', '<p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', 'UPDATE'),
(72, 36, 1, '2025-11-19 04:05:03', NULL, '<p>wdqqwdqwd</p>', NULL, 'UPDATE'),
(73, 35, 1, '2025-11-19 07:31:29', NULL, '<p>asdasdaasdasd</p>', NULL, 'UPDATE'),
(74, 35, 1, '2025-11-19 07:31:55', NULL, '<p>000</p>', NULL, 'UPDATE'),
(75, 37, 3, '2025-11-19 07:57:50', NULL, '<p>dfhsdhdfh</p>', NULL, 'UPDATE'),
(76, 35, 1, '2025-11-21 06:21:16', NULL, '<p>001</p>', NULL, 'UPDATE'),
(77, 41, 1, '2025-11-21 08:23:47', NULL, '<p>Plan 2 para Carlos</p>', NULL, 'UPDATE'),
(78, 41, 3, '2025-11-21 09:10:21', NULL, '<p>Plan 2 para Carlos</p>', NULL, 'UPDATE'),
(79, 43, 3, '2025-11-21 09:11:35', NULL, '<p>Plan 2 para Carlos</p>', NULL, 'UPDATE'),
(80, 43, 3, '2025-11-21 09:11:53', NULL, '<p>Plan 2 para Carlos</p>', NULL, 'UPDATE'),
(81, 35, 1, '2025-11-21 09:21:51', NULL, '<p>0012</p>', NULL, 'UPDATE'),
(82, 35, 1, '2025-11-21 09:22:03', NULL, '<p>0012</p>', NULL, 'UPDATE'),
(83, 43, 3, '2025-11-21 09:23:11', NULL, '<p>Plan 2 para Carlos</p>', NULL, 'UPDATE'),
(84, 43, 1, '2025-11-21 09:24:21', NULL, '<p>Plan 2 para Carlos</p>', NULL, 'UPDATE'),
(85, 43, 1, '2025-11-21 09:25:30', NULL, '<p>Plan 2 para Carlos</p>', NULL, 'UPDATE'),
(86, 43, 1, '2025-11-21 09:26:25', NULL, '<p>Plan 2 para Carlos</p>', NULL, 'UPDATE'),
(87, 44, 3, '2025-11-21 09:45:19', NULL, '<p>Tratamiento aquí</p>', NULL, 'UPDATE'),
(88, 44, 3, '2025-11-21 09:45:19', NULL, '<p>Tratamiento aquí</p>', NULL, 'UPDATE'),
(93, 44, 1, '2025-11-21 09:49:50', NULL, '<p>Tratamiento aquí</p>', NULL, 'UPDATE'),
(94, 44, 1, '2025-11-21 09:49:50', NULL, '<p>Tratamiento aquí</p>', NULL, 'UPDATE'),
(95, 44, 1, '2025-11-21 09:50:16', NULL, '<p>Tratamiento aquí</p>', NULL, 'UPDATE'),
(96, 44, 1, '2025-11-21 09:50:16', NULL, '<p>Tratamiento aquí 2</p>', NULL, 'UPDATE'),
(97, 44, 1, '2025-11-21 09:51:06', NULL, '<p>Tratamiento aquí 2</p>', NULL, 'UPDATE'),
(98, 44, 1, '2025-11-21 09:51:06', NULL, '<p>Tratamiento aquí 2</p>', NULL, 'UPDATE'),
(99, 44, 3, '2025-11-21 09:51:58', NULL, '<p>Tratamiento aquí 2</p>', NULL, 'UPDATE'),
(100, 44, 3, '2025-11-21 09:51:58', NULL, '<p>Tratamiento aquí 2</p>', NULL, 'UPDATE'),
(101, 44, 3, '2025-11-21 09:58:11', NULL, '<p>Tratamiento aquí 2</p>', NULL, 'UPDATE'),
(102, 35, 1, '2026-01-13 06:11:54', NULL, '<p>0012</p>', NULL, 'UPDATE'),
(103, 35, 1, '2026-01-13 06:35:24', NULL, '<p>00123</p>', NULL, 'UPDATE'),
(104, 35, 1, '2026-01-13 06:50:04', NULL, '<p>00123</p>', NULL, 'UPDATE');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_diagnosticos`
--

CREATE TABLE `historial_diagnosticos` (
  `id` int(11) NOT NULL,
  `id_historial` int(11) NOT NULL COMMENT 'Referencia a historial_clinico',
  `id_diagnostico` int(11) NOT NULL COMMENT 'Referencia a la tabla diagnosticos'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `historial_diagnosticos`
--

INSERT INTO `historial_diagnosticos` (`id`, `id_historial`, `id_diagnostico`) VALUES
(27, 8, 1),
(28, 8, 3),
(29, 10, 2),
(26, 11, 3),
(30, 39, 3),
(31, 40, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_items`
--

CREATE TABLE `historial_items` (
  `id` int(11) NOT NULL,
  `id_historial` int(11) NOT NULL COMMENT 'Referencia a historial_clinico',
  `tipo_item` enum('Producto','Servicio') NOT NULL,
  `id_item` int(11) NOT NULL COMMENT 'ID de la tabla productos o servicios',
  `descripcion_item` varchar(255) NOT NULL COMMENT 'Nombre del item al momento de guardarlo',
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `precio_unitario` decimal(10,2) NOT NULL DEFAULT 0.00,
  `descuento` decimal(10,2) NOT NULL DEFAULT 0.00,
  `id_isv` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `historial_items`
--

INSERT INTO `historial_items` (`id`, `id_historial`, `tipo_item`, `id_item`, `descripcion_item`, `cantidad`, `precio_unitario`, `descuento`, `id_isv`) VALUES
(18, 11, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1),
(19, 8, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1),
(24, 17, 'Servicio', 3, 'Nombre de la cirugía', 1, 5000.00, 0.00, 1),
(25, 17, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1),
(26, 18, 'Servicio', 3, 'Nombre de la cirugía', 1, 5000.00, 0.00, 1),
(28, 19, 'Servicio', 3, 'Nombre de la cirugía', 1, 5000.00, 0.00, 1),
(29, 20, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1),
(30, 21, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1),
(32, 22, 'Servicio', 3, 'Nombre de la cirugía', 1, 5000.00, 0.00, 1),
(33, 22, 'Producto', 1, 'Nombre del producto', 1, 130.00, 0.00, 1),
(34, 23, 'Producto', 1, 'Nombre del producto', 2, 130.00, 0.00, 1),
(35, 24, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1),
(36, 25, 'Producto', 1, 'Nombre del producto', 1, 130.00, 0.00, 1),
(37, 26, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1),
(39, 28, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1),
(40, 29, 'Servicio', 3, 'Nombre de la cirugía', 1, 5000.00, 0.00, 1),
(41, 30, 'Servicio', 3, 'Nombre de la cirugía', 1, 5000.00, 0.00, 1),
(42, 31, 'Servicio', 3, 'Nombre de la cirugía', 1, 5000.00, 0.00, 1),
(43, 32, 'Servicio', 3, 'Nombre de la cirugía', 1, 5000.00, 0.00, 1),
(44, 33, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1),
(45, 34, 'Servicio', 3, 'Nombre de la cirugía', 1, 5000.00, 0.00, 1),
(48, 10, 'Servicio', 3, 'Nombre de la cirugía', 1, 5000.00, 0.00, 1),
(49, 36, 'Servicio', 3, 'Nombre de la cirugía', 1, 5000.00, 0.00, 1),
(50, 36, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1),
(52, 38, 'Servicio', 3, 'Nombre de la cirugía', 1, 5000.00, 0.00, 1),
(55, 37, 'Servicio', 3, 'Nombre de la cirugía', 1, 5000.00, 0.00, 1),
(56, 39, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1),
(58, 40, 'Producto', 1, 'Nombre del producto', 1, 130.00, 0.00, 1),
(60, 42, 'Producto', 1, 'Nombre del producto', 1, 130.00, 0.00, 1),
(61, 41, 'Producto', 1, 'Nombre del producto', 1, 130.00, 0.00, 1),
(74, 43, 'Producto', 1, 'Nombre del producto', 2, 130.00, 0.00, 1),
(75, 43, 'Servicio', 3, 'Nombre de la cirugía', 1, 5000.00, 0.00, 1),
(76, 43, 'Servicio', 2, 'Sutura de Herida', 1, 800.00, 0.00, 1),
(93, 44, 'Producto', 1, 'Nombre del producto', 2, 130.00, 0.00, 1),
(94, 45, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1),
(95, 46, 'Servicio', 3, 'Nombre de la cirugía', 1, 5000.00, 0.00, 1),
(96, 48, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1),
(101, 35, 'Servicio', 1, 'Consulta General', 1, 500.00, 0.00, 1),
(102, 35, 'Producto', 1, 'Nombre del producto', 1, 130.00, 0.00, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_refraccion`
--

CREATE TABLE `historial_refraccion` (
  `id` int(11) NOT NULL,
  `id_historial` int(11) NOT NULL COMMENT 'Vincula con la entrada de historial_clinico',
  `tipo_refraccion` enum('Refracción Actual','Lentes Anteriores','Lentes de Contacto') NOT NULL DEFAULT 'Refracción Actual',
  `od_esfera` decimal(5,2) DEFAULT NULL,
  `od_cilindro` decimal(5,2) DEFAULT NULL,
  `od_eje` int(3) DEFAULT NULL,
  `od_av` varchar(20) DEFAULT NULL COMMENT 'Agudeza Visual (ej. 20/20)',
  `os_esfera` decimal(5,2) DEFAULT NULL,
  `os_cilindro` decimal(5,2) DEFAULT NULL,
  `os_eje` int(3) DEFAULT NULL,
  `os_av` varchar(20) DEFAULT NULL COMMENT 'Agudeza Visual (ej. 20/20)',
  `add` decimal(5,2) DEFAULT NULL COMMENT 'Adición para bifocales/progresivos',
  `observaciones` text DEFAULT NULL COMMENT 'Ej. DIP, tipo de lente, etc.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `historial_refraccion`
--

INSERT INTO `historial_refraccion` (`id`, `id_historial`, `tipo_refraccion`, `od_esfera`, `od_cilindro`, `od_eje`, `od_av`, `os_esfera`, `os_cilindro`, `os_eje`, `os_av`, `add`, `observaciones`) VALUES
(1, 8, 'Refracción Actual', 1.25, 0.75, 90, '15/20', -1.00, -0.75, 100, '20/25', 2.00, 'Sin observaciones'),
(2, 7, 'Refracción Actual', 0.50, 0.50, 20, '20/20', -0.50, -0.50, 30, '20/25', 0.75, 'Sin observaciones'),
(3, 10, 'Refracción Actual', 0.00, 0.00, 0, NULL, 0.00, 0.00, 0, NULL, 0.00, NULL),
(4, 11, 'Refracción Actual', 0.00, 0.00, 0, '', 0.00, 0.00, 0, '', 0.00, ''),
(7, 14, 'Refracción Actual', 0.00, 0.00, 0, '', 0.00, 0.00, 0, '', 0.00, ''),
(8, 15, 'Refracción Actual', 0.00, 0.00, 0, '', 0.00, 0.00, 0, '', 0.00, ''),
(9, 16, 'Refracción Actual', 0.00, 0.00, 0, '', 0.00, 0.00, 0, '', 0.00, ''),
(10, 17, 'Refracción Actual', 0.00, 0.00, 0, '', 0.00, 0.00, 0, '', 0.00, ''),
(11, 18, 'Refracción Actual', 0.00, 0.00, 0, '', 0.00, 0.00, 0, '', 0.00, ''),
(12, 19, 'Refracción Actual', 0.00, 0.00, 0, '', 0.00, 0.00, 0, '', 0.00, ''),
(13, 20, 'Refracción Actual', 0.00, 0.00, 0, '', 0.00, 0.00, 0, '', 0.00, ''),
(14, 21, 'Refracción Actual', 0.00, 0.00, 0, '', 0.00, 0.00, 0, '', 0.00, ''),
(15, 22, 'Refracción Actual', 0.00, 0.00, 0, '', 0.00, 0.00, 0, '', 0.00, ''),
(16, 23, 'Refracción Actual', 0.00, 0.00, 0, '', 0.00, 0.00, 0, '', 0.00, ''),
(17, 24, 'Refracción Actual', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(18, 25, 'Refracción Actual', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(19, 26, 'Refracción Actual', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(21, 28, 'Refracción Actual', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(22, 29, 'Refracción Actual', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(23, 30, 'Refracción Actual', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(24, 31, 'Refracción Actual', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(25, 32, 'Refracción Actual', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(26, 33, 'Refracción Actual', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(27, 34, 'Refracción Actual', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(28, 35, 'Refracción Actual', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(29, 36, 'Refracción Actual', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(30, 37, 'Refracción Actual', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(31, 38, 'Refracción Actual', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(32, 39, 'Refracción Actual', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(33, 40, 'Refracción Actual', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(34, 41, 'Refracción Actual', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(35, 42, 'Refracción Actual', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(36, 43, 'Refracción Actual', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(37, 44, 'Refracción Actual', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(38, 45, 'Refracción Actual', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(39, 46, 'Refracción Actual', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(40, 47, 'Refracción Actual', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(41, 48, 'Refracción Actual', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(42, 49, 'Refracción Actual', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `medicos`
--

CREATE TABLE `medicos` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `id_especialidad` int(11) DEFAULT NULL,
  `telefono` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL,
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `medicos`
--

INSERT INTO `medicos` (`id`, `id_usuario`, `nombres`, `apellidos`, `id_especialidad`, `telefono`, `email`, `estado`) VALUES
(2, 3, 'Luis', 'Galindo', 1, '9554-5546', 'luisgalindos@hotmail.com', 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modulos`
--

CREATE TABLE `modulos` (
  `id` int(11) NOT NULL,
  `nombre_modulo` varchar(50) NOT NULL COMMENT 'Nombre técnico, ej: pacientes',
  `nombre_display` varchar(50) NOT NULL COMMENT 'Nombre para mostrar, ej: Pacientes',
  `categoria` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `modulos`
--

INSERT INTO `modulos` (`id`, `nombre_modulo`, `nombre_display`, `categoria`) VALUES
(1, 'pacientes', 'Pacientes', 'Clínica'),
(2, 'citas', 'Citas', 'Clínica'),
(3, 'historial', 'Historial Clínico', 'Clínica'),
(4, 'medicos', 'Médicos', 'Clínica'),
(5, 'facturacion', 'Facturación', 'Ventas'),
(6, 'cotizaciones', 'Cotizaciones', 'Ventas'),
(7, 'cuentas_por_cobrar', 'Cuentas por Cobrar', 'Ventas'),
(8, 'notas_credito', 'Notas de Crédito', 'Ventas'),
(9, 'inventario', 'Movimientos', 'Inventario'),
(10, 'productos', 'Productos', 'Inventario'),
(11, 'servicios', 'Servicios', 'Inventario'),
(12, 'compras', 'Compras', 'Inventario'),
(13, 'proveedores', 'Proveedores', 'Inventario'),
(14, 'reporte_stock', 'Reporte de Stock', 'Reportes'),
(15, 'reportes_clinicos', 'Reportes Clínicos', 'Reportes'),
(16, 'reportes_medicos', 'Reportes Médicos', 'Reportes'),
(17, 'reportes_ventas', 'Reportes de Ventas', 'Reportes'),
(18, 'configuracion', 'Ajustes Generales', 'Configuración'),
(19, 'usuarios', 'Usuarios', 'Configuración'),
(20, 'grupos', 'Grupos y Permisos', 'Configuración'),
(21, 'dashboard_settings', 'Dashboard', 'Configuración'),
(22, 'configuracion_facturacion', 'Facturación (Config)', 'Configuración'),
(23, 'textos_predefinidos', 'Textos Predefinidos', 'Configuración'),
(24, 'especialidades', 'Especialidades', 'Catálogos'),
(25, 'aseguradoras', 'Aseguradoras', 'Catálogos'),
(26, 'diagnosticos', 'Diagnósticos (IDx)', 'Catálogos'),
(27, 'tipos_isv', 'Tipos de ISV', 'Catálogos'),
(28, 'categorias_producto', 'Categorías Producto', 'Catálogos'),
(29, 'categorias_servicio', 'Categorías Servicio', 'Catálogos'),
(30, 'archivo_categorias', 'Categorías Archivos', 'Catálogos'),
(31, 'consulta_plantillas', 'Plantillas Consulta', 'Catálogos'),
(32, 'tipos_consulta', 'Tipos de Consulta', 'Catálogos'),
(33, 'tipos_cirugia', 'Tipos de Cirugía', 'Catálogos');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_inventario`
--

CREATE TABLE `movimientos_inventario` (
  `id` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `tipo_movimiento` enum('Entrada','Salida','Ajuste','Venta') NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) DEFAULT NULL COMMENT 'Precio de compra para las entradas',
  `fecha_movimiento` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_usuario` int(11) NOT NULL,
  `id_proveedor` int(11) DEFAULT NULL,
  `notas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `movimientos_inventario`
--

INSERT INTO `movimientos_inventario` (`id`, `id_producto`, `tipo_movimiento`, `cantidad`, `precio_unitario`, `fecha_movimiento`, `id_usuario`, `id_proveedor`, `notas`) VALUES
(1, 1, 'Entrada', 10, 100.00, '2025-08-18 10:28:31', 1, 1, 'Prueba 1'),
(2, 1, 'Entrada', 1, 100.00, '2025-08-19 05:46:56', 1, 1, 'Entrada por Compra #1'),
(3, 1, 'Entrada', 1, 100.00, '2025-08-19 05:46:56', 1, 1, 'Entrada por Compra #1'),
(4, 1, 'Entrada', 1, 100.00, '2025-08-19 05:54:38', 1, 1, 'Entrada por Compra #2'),
(5, 1, 'Entrada', 1, 90.00, '2025-08-19 08:44:08', 1, 1, 'Entrada por Compra #3'),
(6, 1, 'Entrada', 10, 140.00, '2025-08-20 08:14:54', 1, 1, 'Entrada por Compra #4'),
(7, 1, 'Entrada', 1, 150.00, '2025-08-21 07:11:45', 1, 1, 'Entrada por Compra C-00006'),
(8, 1, 'Salida', 1, 0.00, '2025-08-21 07:49:24', 1, 0, 'Ejemplo'),
(9, 1, 'Salida', 5, 0.00, '2025-08-21 08:00:41', 1, 0, 'Ejemplo Salida 1'),
(10, 1, 'Entrada', 1, 0.00, '2025-08-21 08:01:08', 1, 0, 'Ejemplo Entrada 1'),
(12, 1, 'Entrada', 1, 100.00, '2025-08-21 08:13:38', 1, 1, 'Ejemplo Entrada 2'),
(13, 1, 'Entrada', 1, 10.00, '2025-08-21 08:18:12', 1, 1, 'Ejemplo Entrada 3'),
(14, 1, 'Salida', 2, 0.00, '2025-08-21 08:24:47', 1, 0, 'Ejemplo Salida 2'),
(15, 1, 'Entrada', 10, 0.00, '2025-08-21 08:25:21', 1, 0, 'Ejemplo Entrada 4'),
(16, 1, 'Entrada', 10, 100.00, '2025-08-21 08:26:40', 1, 1, 'Entrada por Compra C-00007'),
(17, 1, 'Entrada', 10, 0.00, '2025-08-21 08:28:04', 1, 0, 'Ejemplo Entrada 5'),
(18, 1, 'Entrada', 5, 0.00, '2025-08-21 08:28:04', 1, 0, 'Ejemplo Entrada 5'),
(19, 1, 'Ajuste', -2, 0.00, '2025-08-24 02:37:12', 1, 0, 'Ajuste de prueba'),
(20, 1, 'Ajuste', -1, 0.00, '2025-08-24 02:41:07', 1, 0, 'Ajuste de prueba 2'),
(21, 1, 'Venta', -3, 0.00, '2025-11-18 14:35:07', 1, 0, 'Factura #F-00120'),
(22, 1, 'Venta', -5, 0.00, '2025-11-18 14:47:20', 1, 0, 'Factura #F-00119'),
(23, 1, 'Venta', -2, 0.00, '2025-11-18 14:53:36', 1, 0, 'Factura #F-00118'),
(24, 1, 'Venta', -1, 0.00, '2025-11-18 14:55:16', 1, 0, 'Factura #F-00121'),
(25, 1, 'Venta', -1, NULL, '2025-11-19 10:01:28', 1, NULL, 'Factura #F-00123'),
(26, 1, 'Venta', -5, NULL, '2025-11-19 10:02:31', 1, NULL, 'Factura #F-00124'),
(27, 1, 'Venta', -1, NULL, '2025-11-19 11:30:18', 1, NULL, 'Factura #F-00134'),
(28, 1, 'Venta', -1, NULL, '2025-11-19 08:10:28', 1, NULL, 'Factura #F-00137'),
(29, 1, 'Venta', -1, NULL, '2025-11-21 08:13:27', 1, NULL, 'Factura #F-00139');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notas_credito`
--

CREATE TABLE `notas_credito` (
  `id` int(11) NOT NULL,
  `correlativo` varchar(20) NOT NULL,
  `id_factura_asociada` int(11) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `fecha_emision` date NOT NULL,
  `motivo` text DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `isv_total` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `estado` enum('Aplicada','Anulada') NOT NULL DEFAULT 'Aplicada'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notas_credito`
--

INSERT INTO `notas_credito` (`id`, `correlativo`, `id_factura_asociada`, `id_paciente`, `fecha_emision`, `motivo`, `subtotal`, `isv_total`, `total`, `id_usuario`, `estado`) VALUES
(1, 'NC-00001', 21, 1, '2025-08-27', 'Prueba', 5000.00, 0.00, 5000.00, 1, 'Aplicada'),
(2, 'NC-00002', 14, 1, '2025-08-28', 'Ejemplo', 760.00, 0.00, 760.00, 1, 'Aplicada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nota_credito_detalle`
--

CREATE TABLE `nota_credito_detalle` (
  `id` int(11) NOT NULL,
  `id_nota_credito` int(11) NOT NULL,
  `tipo_item` enum('Producto','Servicio') NOT NULL,
  `id_item` int(11) NOT NULL,
  `descripcion_item` varchar(255) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `id_isv` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `nota_credito_detalle`
--

INSERT INTO `nota_credito_detalle` (`id`, `id_nota_credito`, `tipo_item`, `id_item`, `descripcion_item`, `cantidad`, `precio_unitario`, `subtotal`, `id_isv`) VALUES
(1, 1, 'Servicio', 3, 'Nombre de la cirugía', 1, 5000.00, 5000.00, 1),
(2, 2, 'Servicio', 1, 'Consulta General', 1, 500.00, 500.00, 1),
(3, 2, 'Producto', 1, 'Nombre del producto', 1, 130.00, 130.00, 1),
(4, 2, 'Producto', 1, 'Nombre del producto', 1, 130.00, 130.00, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pacientes`
--

CREATE TABLE `pacientes` (
  `id` int(11) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `numero_identidad` varchar(20) NOT NULL,
  `sexo` varchar(10) NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `telefono` varchar(15) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `fecha_nacimiento` date NOT NULL,
  `tiene_seguro` enum('Sí','No') NOT NULL DEFAULT 'No',
  `id_aseguradora` int(11) DEFAULT NULL,
  `numero_poliza` varchar(50) DEFAULT NULL,
  `antecedente_dm` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Diabetes Mellitus',
  `antecedente_hta` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Hipertensión Arterial',
  `antecedente_glaucoma` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Glaucoma',
  `antecedente_asma` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Asma',
  `antecedente_cirugias` text DEFAULT NULL COMMENT 'Cirugías previas (oculares o generales)',
  `antecedente_trauma` text DEFAULT NULL COMMENT 'Traumas oculares previos',
  `antecedente_otros` text DEFAULT NULL COMMENT 'Otras enfermedades relevantes',
  `alergias` text DEFAULT NULL COMMENT 'Alergias a medicamentos u otras',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pacientes`
--

INSERT INTO `pacientes` (`id`, `nombres`, `apellidos`, `numero_identidad`, `sexo`, `direccion`, `telefono`, `email`, `fecha_nacimiento`, `tiene_seguro`, `id_aseguradora`, `numero_poliza`, `antecedente_dm`, `antecedente_hta`, `antecedente_glaucoma`, `antecedente_asma`, `antecedente_cirugias`, `antecedente_trauma`, `antecedente_otros`, `alergias`, `fecha_creacion`, `observaciones`) VALUES
(1, 'Juan Carlos', 'Pérez Gómez', '0801-1985-123456', 'Masculino', 'Col. Centro, Calle Principal #123', '9635-4939', 'juan.perez@example.com', '1985-05-20', 'No', NULL, NULL, 1, 1, 1, 0, NULL, NULL, NULL, NULL, '2025-08-07 05:13:22', 'Aquí observaciones.'),
(2, 'Andres', 'Galindo', '0501-1984-08145', 'Masculino', 'dir', '9635-4938', 'andresgalindo@mail.com', '1984-05-24', 'No', 0, '', 1, 1, 0, 0, '', '', '', '', '2025-08-28 05:51:31', 'obs'),
(3, 'Juan José', 'Gómez', '0501-1980-01478', 'Masculino', 'Dirección de Juan Gómez', '9874-5874', 'mgomez@mail.com', '1980-08-03', 'No', 0, '', 1, 0, 1, 0, '', '', '', '', '2025-08-28 06:00:33', 'Observaciones de Juan Gómez'),
(4, 'Jorge', 'Campos', '0101-1960-23456', 'Masculino', 'Tegucigalpa', '9876-5433', 'camposjorge@gmail.com', '1960-06-17', 'No', 0, '', 1, 0, 1, 0, '', '', '', '', '2025-11-15 10:38:38', ''),
(5, 'José Luis', 'Perdomo', '0000-000-000000', 'Masculino', 'N/A', '0501-1144', '', '1990-08-20', 'No', NULL, NULL, 0, 0, 1, 0, NULL, NULL, NULL, NULL, '2025-11-18 02:20:35', 'Obs'),
(6, 'Ramon Antonio', 'Galeano', '151515-1551515', 'Masculino', 'N/A', 'N/A', '', '1980-12-20', 'No', 0, '', 0, 0, 0, 0, '', '', '', '', '2025-11-18 02:46:32', ''),
(7, 'Carlos Alberto', 'Pavon Plumer', '0504-1970-58479', 'Masculino', 'San Pedro Sula', '9635-0123', 'carlosp@gmail.com', '1970-02-02', 'No', NULL, NULL, 1, 0, 1, 0, NULL, NULL, NULL, NULL, '2025-11-21 08:15:31', NULL),
(8, 'Luis', 'Soriano', '010203-010203', 'Masculino', 'N/A', 'N/A', '', '1990-10-10', 'No', NULL, NULL, 0, 0, 0, 0, NULL, NULL, NULL, NULL, '2025-11-22 03:20:44', NULL),
(9, 'andres', 'Soriano', '0801-1985-12322', 'No especif', 'N/A', 'N/A', '', '1900-01-01', 'No', NULL, NULL, 0, 0, 0, 0, NULL, NULL, NULL, NULL, '2026-01-13 06:55:03', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` int(11) NOT NULL,
  `id_factura` int(11) NOT NULL,
  `fecha_pago` timestamp NOT NULL DEFAULT current_timestamp(),
  `monto_total` decimal(10,2) NOT NULL,
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`id`, `id_factura`, `fecha_pago`, `monto_total`, `id_usuario`) VALUES
(1, 14, '2025-08-22 11:25:37', 1280.00, 1),
(2, 13, '2025-08-22 11:30:26', 630.00, 1),
(3, 22, '2025-08-23 08:01:15', 149.50, 1),
(4, 15, '2025-08-23 08:03:11', 575.00, 1),
(5, 21, '2025-08-23 08:22:02', 5000.00, 1),
(6, 16, '2025-08-23 09:41:54', 500.00, 1),
(7, 17, '2025-08-23 09:47:47', 500.00, 1),
(8, 18, '2025-08-24 06:17:24', 400.00, 1),
(9, 23, '2025-08-25 09:33:34', 647.50, 1),
(10, 24, '2025-08-25 09:46:13', 500.00, 1),
(11, 25, '2025-08-25 09:55:54', 130.00, 1),
(12, 26, '2025-08-25 09:56:57', 130.00, 1),
(13, 19, '2025-08-25 10:04:52', 500.00, 1),
(14, 27, '2025-08-25 10:15:09', 250.00, 1),
(15, 20, '2025-08-26 03:09:16', 500.00, 1),
(16, 28, '2025-08-26 06:38:33', 549.50, 1),
(17, 29, '2025-08-26 06:55:31', 150.00, 1),
(18, 30, '2025-08-26 07:23:47', 500.00, 1),
(19, 28, '2025-08-27 08:30:19', 649.50, 1),
(20, 12, '2025-08-27 08:32:00', 500.00, 1),
(21, 20, '2025-08-27 08:33:12', 500.00, 1),
(22, 32, '2025-08-28 01:43:02', 100.00, 1),
(23, 34, '2025-11-14 06:21:24', 400.00, 1),
(24, 35, '2025-11-17 04:30:36', 390.00, 1),
(25, 41, '2025-11-18 07:35:57', 910.00, 1),
(26, 40, '2025-11-18 07:47:20', 650.00, 1),
(27, 39, '2025-11-18 07:53:36', 5260.00, 1),
(28, 42, '2025-11-18 07:55:16', 130.00, 1),
(29, 44, '2025-11-19 03:01:28', 130.00, 1),
(30, 45, '2025-11-19 03:02:31', 650.00, 1),
(31, 56, '2025-11-19 04:30:18', 130.00, 1),
(32, 55, '2025-11-19 05:04:08', 5000.00, 1),
(33, 58, '2025-11-19 05:20:58', 3000.00, 1),
(34, 58, '2025-11-19 06:28:44', 1000.00, 1),
(35, 57, '2025-11-19 06:30:02', 100.00, 1),
(36, 57, '2025-11-19 06:30:27', 400.00, 1),
(37, 54, '2025-11-19 06:30:56', 2000.00, 1),
(38, 54, '2025-11-19 06:31:48', 1000.00, 1),
(39, 54, '2025-11-19 06:32:10', 500.00, 1),
(40, 54, '2025-11-19 06:32:41', 500.00, 1),
(41, 54, '2025-11-19 06:36:09', 50.00, 1),
(42, 58, '2025-11-19 06:41:53', 1000.00, 1),
(43, 54, '2025-11-19 06:44:40', 950.00, 1),
(44, 53, '2025-11-19 07:04:36', 250.00, 1),
(45, 53, '2025-11-19 07:04:58', 250.00, 1),
(46, 52, '2025-11-19 07:26:37', 5000.00, 1),
(47, 59, '2025-11-19 07:38:06', 50.00, 1),
(48, 60, '2025-11-19 08:00:22', 200.00, 1),
(49, 59, '2025-11-19 08:10:28', 80.00, 1),
(50, 61, '2025-11-21 08:13:13', 100.00, 1),
(51, 61, '2025-11-21 08:13:27', 30.00, 1),
(52, 74, '2025-11-22 04:33:21', 200.00, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pago_detalle`
--

CREATE TABLE `pago_detalle` (
  `id` int(11) NOT NULL,
  `id_pago` int(11) NOT NULL,
  `forma_pago` enum('Efectivo','Tarjeta','Transferencia') NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `referencia` varchar(100) DEFAULT NULL COMMENT 'N° de voucher o transacción'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pago_detalle`
--

INSERT INTO `pago_detalle` (`id`, `id_pago`, `forma_pago`, `monto`, `referencia`) VALUES
(1, 1, 'Efectivo', 500.00, ''),
(2, 1, 'Tarjeta', 780.00, '478541'),
(3, 2, 'Transferencia', 630.00, '6546541'),
(4, 3, 'Efectivo', 100.00, ''),
(5, 3, 'Tarjeta', 49.50, '45545'),
(6, 4, 'Efectivo', 500.00, ''),
(7, 4, 'Tarjeta', 75.00, '7878'),
(8, 5, 'Efectivo', 4000.00, ''),
(9, 5, 'Tarjeta', 1000.00, '1515151'),
(10, 6, 'Efectivo', 500.00, ''),
(11, 7, 'Tarjeta', 500.00, '9875648'),
(12, 8, 'Efectivo', 200.00, ''),
(13, 8, 'Tarjeta', 200.00, '21000'),
(14, 9, 'Efectivo', 647.50, ''),
(15, 10, 'Efectivo', 500.00, ''),
(16, 11, 'Efectivo', 130.00, ''),
(17, 12, 'Efectivo', 130.00, ''),
(18, 13, 'Efectivo', 500.00, ''),
(19, 14, 'Efectivo', 250.00, ''),
(20, 17, 'Efectivo', 150.00, ''),
(21, 18, 'Efectivo', 500.00, ''),
(22, 19, 'Efectivo', 649.50, ''),
(23, 20, 'Efectivo', 500.00, ''),
(24, 21, 'Efectivo', 500.00, ''),
(25, 22, 'Efectivo', 100.00, ''),
(26, 23, 'Efectivo', 400.00, ''),
(27, 24, 'Efectivo', 390.00, ''),
(28, 25, 'Efectivo', 910.00, ''),
(29, 26, 'Efectivo', 500.00, ''),
(30, 26, 'Tarjeta', 150.00, '54546'),
(31, 27, 'Efectivo', 5260.00, ''),
(32, 28, 'Efectivo', 130.00, ''),
(33, 29, 'Efectivo', 130.00, ''),
(34, 30, 'Efectivo', 650.00, ''),
(35, 31, 'Efectivo', 130.00, ''),
(36, 32, 'Efectivo', 5000.00, ''),
(37, 33, 'Efectivo', 1000.00, ''),
(38, 33, 'Tarjeta', 2000.00, '123'),
(39, 34, 'Efectivo', 1000.00, ''),
(40, 35, 'Efectivo', 100.00, ''),
(41, 36, 'Tarjeta', 400.00, '1414'),
(42, 37, 'Efectivo', 2000.00, ''),
(43, 38, 'Tarjeta', 1000.00, '55'),
(44, 39, 'Efectivo', 500.00, ''),
(45, 40, 'Transferencia', 500.00, '11'),
(46, 41, 'Transferencia', 50.00, '1'),
(47, 42, 'Efectivo', 1000.00, ''),
(48, 43, 'Efectivo', 950.00, ''),
(49, 44, 'Efectivo', 250.00, ''),
(50, 45, 'Efectivo', 250.00, ''),
(51, 46, 'Efectivo', 5000.00, ''),
(52, 47, 'Efectivo', 50.00, ''),
(53, 48, 'Efectivo', 200.00, ''),
(54, 49, 'Efectivo', 80.00, ''),
(55, 50, 'Efectivo', 100.00, ''),
(56, 51, 'Efectivo', 30.00, ''),
(57, 52, 'Tarjeta', 200.00, '0012');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos`
--

CREATE TABLE `permisos` (
  `id` int(11) NOT NULL,
  `id_grupo` int(11) NOT NULL,
  `id_modulo` int(11) NOT NULL,
  `ver` tinyint(1) NOT NULL DEFAULT 0,
  `crear` tinyint(1) NOT NULL DEFAULT 0,
  `editar` tinyint(1) NOT NULL DEFAULT 0,
  `borrar` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `permisos`
--

INSERT INTO `permisos` (`id`, `id_grupo`, `id_modulo`, `ver`, `crear`, `editar`, `borrar`) VALUES
(11, 2, 1, 1, 1, 1, 0),
(12, 2, 2, 1, 1, 1, 0),
(13, 2, 3, 1, 0, 0, 0),
(14, 2, 4, 1, 0, 0, 0),
(15, 2, 5, 1, 1, 1, 0),
(16, 2, 6, 0, 0, 0, 0),
(17, 2, 7, 0, 0, 0, 0),
(18, 2, 8, 0, 0, 0, 0),
(19, 2, 9, 0, 0, 0, 0),
(20, 2, 10, 1, 0, 0, 0),
(41, 3, 1, 1, 1, 1, 0),
(42, 3, 2, 1, 1, 1, 0),
(43, 3, 3, 1, 0, 0, 0),
(44, 3, 4, 1, 0, 0, 0),
(45, 3, 5, 1, 0, 0, 0),
(46, 3, 6, 1, 0, 0, 0),
(47, 3, 7, 1, 0, 0, 0),
(48, 3, 8, 1, 0, 0, 0),
(49, 3, 9, 1, 0, 0, 0),
(50, 3, 10, 1, 1, 1, 0),
(73, 3, 11, 1, 0, 0, 0),
(211, 4, 1, 1, 0, 0, 0),
(212, 4, 2, 1, 0, 0, 0),
(213, 4, 3, 1, 0, 0, 0),
(214, 4, 4, 1, 0, 0, 0),
(215, 4, 5, 1, 0, 0, 0),
(216, 4, 6, 1, 0, 0, 0),
(217, 4, 7, 1, 0, 0, 0),
(218, 4, 8, 1, 0, 0, 0),
(219, 4, 9, 1, 0, 0, 0),
(220, 4, 10, 1, 0, 0, 0),
(221, 4, 11, 1, 0, 0, 0),
(222, 4, 12, 1, 0, 0, 0),
(223, 4, 14, 1, 0, 0, 0),
(224, 4, 15, 1, 0, 0, 0),
(225, 4, 16, 1, 0, 0, 0),
(226, 4, 17, 1, 0, 0, 0),
(238, 3, 12, 0, 0, 0, 0),
(239, 3, 14, 0, 0, 0, 0),
(240, 3, 15, 0, 0, 0, 0),
(241, 3, 16, 0, 0, 0, 0),
(242, 3, 17, 0, 0, 0, 0),
(255, 2, 11, 1, 0, 0, 0),
(256, 2, 12, 0, 0, 0, 0),
(257, 2, 14, 1, 0, 0, 0),
(258, 2, 15, 1, 0, 0, 0),
(259, 2, 16, 1, 0, 0, 0),
(260, 2, 17, 1, 0, 0, 0),
(261, 2, 18, 0, 0, 0, 0),
(262, 2, 19, 0, 0, 0, 0),
(283, 4, 18, 1, 0, 0, 0),
(284, 4, 19, 1, 0, 0, 0),
(285, 4, 20, 1, 0, 0, 0),
(286, 4, 21, 1, 0, 0, 0),
(287, 4, 22, 1, 0, 0, 0),
(288, 4, 23, 1, 0, 0, 0),
(313, 4, 24, 1, 0, 0, 0),
(314, 4, 25, 1, 0, 0, 0),
(335, 2, 20, 0, 0, 0, 0),
(336, 2, 21, 0, 0, 0, 0),
(337, 2, 22, 0, 0, 0, 0),
(338, 2, 23, 0, 0, 0, 0),
(339, 2, 24, 1, 1, 1, 0),
(340, 2, 25, 1, 0, 0, 0),
(341, 2, 26, 1, 0, 0, 0),
(342, 2, 27, 0, 0, 0, 0),
(367, 4, 26, 1, 0, 0, 0),
(368, 4, 27, 1, 0, 0, 0),
(380, 2, 13, 0, 0, 0, 0),
(394, 2, 31, 1, 1, 1, 0),
(395, 2, 29, 1, 1, 1, 0),
(396, 2, 30, 1, 0, 0, 0),
(399, 2, 32, 1, 1, 1, 0),
(400, 2, 34, 0, 0, 0, 0),
(401, 2, 33, 1, 1, 1, 0),
(402, 2, 28, 1, 1, 1, 0),
(607, 1, 30, 1, 1, 1, 1),
(608, 1, 25, 1, 1, 1, 1),
(609, 1, 28, 1, 1, 1, 1),
(610, 1, 29, 1, 1, 1, 1),
(611, 1, 2, 1, 1, 1, 1),
(612, 1, 12, 1, 1, 1, 1),
(613, 1, 18, 1, 1, 1, 1),
(614, 1, 22, 1, 1, 1, 1),
(615, 1, 31, 1, 1, 1, 1),
(616, 1, 6, 1, 1, 1, 1),
(617, 1, 7, 1, 1, 1, 1),
(618, 1, 21, 1, 1, 1, 1),
(619, 1, 26, 1, 1, 1, 1),
(620, 1, 24, 1, 1, 1, 1),
(621, 1, 5, 1, 1, 1, 1),
(622, 1, 20, 1, 1, 1, 1),
(623, 1, 3, 1, 1, 1, 1),
(624, 1, 9, 1, 1, 1, 1),
(625, 1, 4, 1, 1, 1, 1),
(626, 1, 8, 1, 1, 1, 1),
(627, 1, 1, 1, 1, 1, 1),
(628, 1, 10, 1, 1, 1, 1),
(629, 1, 13, 1, 1, 1, 1),
(630, 1, 15, 1, 1, 1, 1),
(631, 1, 16, 1, 1, 1, 1),
(632, 1, 17, 1, 1, 1, 1),
(633, 1, 14, 1, 1, 1, 1),
(634, 1, 11, 1, 1, 1, 1),
(635, 1, 23, 1, 1, 1, 1),
(636, 1, 33, 1, 1, 1, 1),
(637, 1, 32, 1, 1, 1, 1),
(638, 1, 27, 1, 1, 1, 1),
(639, 1, 19, 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `codigo` varchar(50) DEFAULT NULL,
  `codigo_barras` varchar(100) DEFAULT NULL,
  `nombre_producto` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `id_categoria` int(11) DEFAULT NULL,
  `stock_actual` int(11) NOT NULL DEFAULT 0,
  `stock_minimo` int(11) NOT NULL DEFAULT 10,
  `id_isv` int(11) DEFAULT NULL,
  `precio_compra` decimal(10,2) NOT NULL DEFAULT 0.00,
  `precio_venta` decimal(10,2) NOT NULL DEFAULT 0.00,
  `unidad_medida` varchar(50) DEFAULT NULL COMMENT 'Ej: Caja, Unidad, Frasco',
  `es_inventariable` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = Sí, 0 = No'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `codigo`, `codigo_barras`, `nombre_producto`, `descripcion`, `id_categoria`, `stock_actual`, `stock_minimo`, `id_isv`, `precio_compra`, `precio_venta`, `unidad_medida`, `es_inventariable`) VALUES
(1, '001122', '0011224455', 'Nombre del producto', 'Descripción larga del producto', 1, 8, 10, 1, 100.00, 130.00, '1 tableta', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL,
  `nombre_proveedor` varchar(150) NOT NULL,
  `contacto` varchar(100) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id`, `nombre_proveedor`, `contacto`, `telefono`, `email`, `estado`) VALUES
(1, 'Proveedor 1', '', '', '', 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios`
--

CREATE TABLE `servicios` (
  `id` int(11) NOT NULL,
  `codigo` varchar(50) DEFAULT NULL,
  `nombre_servicio` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `id_categoria_servicio` int(11) DEFAULT NULL,
  `id_isv` int(11) DEFAULT NULL,
  `precio_venta` decimal(10,2) NOT NULL DEFAULT 0.00,
  `mostrar_en_citas` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = Sí, 0 = No'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `servicios`
--

INSERT INTO `servicios` (`id`, `codigo`, `nombre_servicio`, `descripcion`, `id_categoria_servicio`, `id_isv`, `precio_venta`, `mostrar_en_citas`) VALUES
(1, 'CONS-GEN', 'Consulta General', 'Evaluación médica general.', 1, 1, 500.00, 1),
(2, 'PROC-SUT', 'Sutura de Herida', 'Procedimiento para cerrar heridas menores.', 3, 1, 800.00, 0),
(3, 'NOM-CIR', 'Nombre de la cirugía', 'Aquí la descripción', 2, 1, 5000.00, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `textos_predefinidos`
--

CREATE TABLE `textos_predefinidos` (
  `id` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `contenido` text NOT NULL,
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `textos_predefinidos`
--

INSERT INTO `textos_predefinidos` (`id`, `titulo`, `contenido`, `estado`) VALUES
(1, 'Plan Básico', '<p><b>Plan a seguir:</b></p><ul><li>Reposo relativo.</li><li>Dieta balanceada.</li></ul>', 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_isv`
--

CREATE TABLE `tipos_isv` (
  `id` int(11) NOT NULL,
  `nombre_isv` varchar(100) NOT NULL,
  `porcentaje` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipos_isv`
--

INSERT INTO `tipos_isv` (`id`, `nombre_isv`, `porcentaje`) VALUES
(1, 'Exento', 0.00),
(2, '15%', 15.00),
(3, '18%', 18.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre_completo` varchar(150) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  `id_grupo` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre_completo`, `usuario`, `password`, `estado`, `id_grupo`) VALUES
(1, 'Administrador del Sistema', 'admin', '$2y$10$QUrg5FHqTd9pZX6rty5tHu3T391A.YfgXgfyfmiLZqNYKU9BTjPHW', 'Activo', 1),
(2, 'Recepción 1', 'recepcion1', '$2y$10$8UTOmZ48QvnHwMwCOlxkKeZAKqU.1jXY9N9Si3GkLbiDDqMMiS02m', 'Activo', 2),
(3, 'Luis Galindo', 'lgalindo', '$2y$10$Sd2eI7eRMuT.ayp8tRd/N.3zeA7caw.1cvCk0DLS7S8PSLxTNpdHS', 'Activo', 3),
(4, 'Nombre técnico', 'tecnico1', '$2y$10$WB9LyCaLUPEMV.9MRi9fbOysRxgun0Xzm/1CXmmJulmy64riy8gF2', 'Activo', 4);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `archivos_historial`
--
ALTER TABLE `archivos_historial`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_historial` (`id_historial`),
  ADD KEY `id_usuario_subida` (`id_usuario_subida`),
  ADD KEY `fk_archivo_categoria` (`id_categoria`),
  ADD KEY `fk_archivo_paciente` (`id_paciente`);

--
-- Indices de la tabla `archivo_categorias`
--
ALTER TABLE `archivo_categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre_categoria` (`nombre_categoria`);

--
-- Indices de la tabla `aseguradoras`
--
ALTER TABLE `aseguradoras`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `categorias_producto`
--
ALTER TABLE `categorias_producto`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre_categoria` (`nombre_categoria`);

--
-- Indices de la tabla `categorias_servicio`
--
ALTER TABLE `categorias_servicio`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre_categoria` (`nombre_categoria`);

--
-- Indices de la tabla `citas`
--
ALTER TABLE `citas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_paciente` (`id_paciente`),
  ADD KEY `id_medico` (`id_medico`);

--
-- Indices de la tabla `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correlativo` (`correlativo`),
  ADD KEY `id_proveedor` (`id_proveedor`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `compras_detalle`
--
ALTER TABLE `compras_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_compra` (`id_compra`),
  ADD KEY `id_producto` (`id_producto`),
  ADD KEY `id_isv` (`id_isv`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `configuracion_facturacion`
--
ALTER TABLE `configuracion_facturacion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `configuracion_notas_credito`
--
ALTER TABLE `configuracion_notas_credito`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `consulta_plantillas`
--
ALTER TABLE `consulta_plantillas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `titulo` (`titulo`);

--
-- Indices de la tabla `cotizaciones`
--
ALTER TABLE `cotizaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_paciente` (`id_paciente`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `cotizacion_detalle`
--
ALTER TABLE `cotizacion_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_cotizacion` (`id_cotizacion`);

--
-- Indices de la tabla `dashboard_widgets`
--
ALTER TABLE `dashboard_widgets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `widget_key` (`widget_key`);

--
-- Indices de la tabla `diagnosticos`
--
ALTER TABLE `diagnosticos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `descripcion` (`descripcion`),
  ADD KEY `codigo` (`codigo`);

--
-- Indices de la tabla `especialidades`
--
ALTER TABLE `especialidades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correlativo` (`correlativo`),
  ADD KEY `id_paciente` (`id_paciente`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `fk_facturas_medico` (`id_medico`),
  ADD KEY `fk_factura_tecnico` (`id_tecnico`);

--
-- Indices de la tabla `factura_detalle`
--
ALTER TABLE `factura_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_factura` (`id_factura`);

--
-- Indices de la tabla `grupos`
--
ALTER TABLE `grupos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre_grupo` (`nombre_grupo`);

--
-- Indices de la tabla `historial_clinico`
--
ALTER TABLE `historial_clinico`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_cita` (`id_cita`),
  ADD KEY `id_paciente` (`id_paciente`),
  ADD KEY `id_medico` (`id_medico`);

--
-- Indices de la tabla `historial_clinico_log`
--
ALTER TABLE `historial_clinico_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_id_historial` (`id_historial`),
  ADD KEY `idx_id_usuario` (`id_usuario_modifica`);

--
-- Indices de la tabla `historial_diagnosticos`
--
ALTER TABLE `historial_diagnosticos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `historial_diagnostico_unico` (`id_historial`,`id_diagnostico`),
  ADD KEY `idx_id_historial` (`id_historial`),
  ADD KEY `idx_id_diagnostico` (`id_diagnostico`);

--
-- Indices de la tabla `historial_items`
--
ALTER TABLE `historial_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_id_historial` (`id_historial`),
  ADD KEY `idx_id_isv` (`id_isv`);

--
-- Indices de la tabla `historial_refraccion`
--
ALTER TABLE `historial_refraccion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_id_historial` (`id_historial`);

--
-- Indices de la tabla `medicos`
--
ALTER TABLE `medicos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_especialidad` (`id_especialidad`);

--
-- Indices de la tabla `modulos`
--
ALTER TABLE `modulos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre_modulo` (`nombre_modulo`);

--
-- Indices de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_producto` (`id_producto`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_proveedor` (`id_proveedor`);

--
-- Indices de la tabla `notas_credito`
--
ALTER TABLE `notas_credito`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correlativo` (`correlativo`),
  ADD KEY `id_factura_asociada` (`id_factura_asociada`),
  ADD KEY `id_paciente` (`id_paciente`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `nota_credito_detalle`
--
ALTER TABLE `nota_credito_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_nota_credito` (`id_nota_credito`);

--
-- Indices de la tabla `pacientes`
--
ALTER TABLE `pacientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_aseguradora` (`id_aseguradora`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_factura` (`id_factura`);

--
-- Indices de la tabla `pago_detalle`
--
ALTER TABLE `pago_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_pago` (`id_pago`);

--
-- Indices de la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `grupo_modulo` (`id_grupo`,`id_modulo`),
  ADD KEY `id_modulo` (`id_modulo`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD UNIQUE KEY `codigo_barras` (`codigo_barras`),
  ADD KEY `id_categoria` (`id_categoria`),
  ADD KEY `id_isv` (`id_isv`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `id_categoria_servicio` (`id_categoria_servicio`),
  ADD KEY `id_isv` (`id_isv`);

--
-- Indices de la tabla `textos_predefinidos`
--
ALTER TABLE `textos_predefinidos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `titulo` (`titulo`);

--
-- Indices de la tabla `tipos_isv`
--
ALTER TABLE `tipos_isv`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD KEY `id_grupo` (`id_grupo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `archivos_historial`
--
ALTER TABLE `archivos_historial`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT de la tabla `archivo_categorias`
--
ALTER TABLE `archivo_categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `aseguradoras`
--
ALTER TABLE `aseguradoras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `categorias_producto`
--
ALTER TABLE `categorias_producto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `categorias_servicio`
--
ALTER TABLE `categorias_servicio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `citas`
--
ALTER TABLE `citas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT de la tabla `compras`
--
ALTER TABLE `compras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `compras_detalle`
--
ALTER TABLE `compras_detalle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `consulta_plantillas`
--
ALTER TABLE `consulta_plantillas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `cotizaciones`
--
ALTER TABLE `cotizaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `cotizacion_detalle`
--
ALTER TABLE `cotizacion_detalle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `dashboard_widgets`
--
ALTER TABLE `dashboard_widgets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `diagnosticos`
--
ALTER TABLE `diagnosticos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `especialidades`
--
ALTER TABLE `especialidades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `facturas`
--
ALTER TABLE `facturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT de la tabla `factura_detalle`
--
ALTER TABLE `factura_detalle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=133;

--
-- AUTO_INCREMENT de la tabla `grupos`
--
ALTER TABLE `grupos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `historial_clinico`
--
ALTER TABLE `historial_clinico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT de la tabla `historial_clinico_log`
--
ALTER TABLE `historial_clinico_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT de la tabla `historial_diagnosticos`
--
ALTER TABLE `historial_diagnosticos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `historial_items`
--
ALTER TABLE `historial_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT de la tabla `historial_refraccion`
--
ALTER TABLE `historial_refraccion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT de la tabla `medicos`
--
ALTER TABLE `medicos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `modulos`
--
ALTER TABLE `modulos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de la tabla `notas_credito`
--
ALTER TABLE `notas_credito`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `nota_credito_detalle`
--
ALTER TABLE `nota_credito_detalle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `pacientes`
--
ALTER TABLE `pacientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT de la tabla `pago_detalle`
--
ALTER TABLE `pago_detalle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=736;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `textos_predefinidos`
--
ALTER TABLE `textos_predefinidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tipos_isv`
--
ALTER TABLE `tipos_isv`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `citas`
--
ALTER TABLE `citas`
  ADD CONSTRAINT `citas_ibfk_1` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `citas_ibfk_2` FOREIGN KEY (`id_medico`) REFERENCES `medicos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

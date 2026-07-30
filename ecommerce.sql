-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 30-07-2026 a las 21:13:40
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `ecommerce`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `imagen`, `activo`, `fecha_creacion`) VALUES
(1, 'Camisetas', 'Camisetas deportivas para hombre y mujer', NULL, 1, '2026-05-12 07:20:02'),
(2, 'Zapatos', 'Calzado deportivo de alto rendimiento', NULL, 1, '2026-05-12 07:20:02'),
(3, 'Pantalonetas', 'Pantalonetas y shorts deportivos', NULL, 1, '2026-05-12 07:20:02'),
(4, 'Accesorios', 'Medias, gorras, bolsos y m??s', NULL, 1, '2026-05-12 07:20:02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cupones`
--

CREATE TABLE `cupones` (
  `id` int(11) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `tipo` enum('porcentaje','monto_fijo') NOT NULL,
  `descuento` decimal(10,2) NOT NULL,
  `usos_maximos` int(11) DEFAULT 1,
  `usos_actuales` int(11) DEFAULT 0,
  `fecha_vencimiento` date DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cupones`
--

INSERT INTO `cupones` (`id`, `codigo`, `tipo`, `descuento`, `usos_maximos`, `usos_actuales`, `fecha_vencimiento`, `activo`, `fecha_creacion`) VALUES
(1, 'BIENVENIDA', 'porcentaje', 10.00, 999, 0, NULL, 1, '2026-05-12 07:20:03'),
(2, 'DESCUENTO20', 'porcentaje', 20.00, 999, 0, NULL, 1, '2026-05-12 07:20:03'),
(3, 'MONTO100', 'monto_fijo', 100.00, 50, 0, NULL, 1, '2026-05-12 07:20:03'),
(4, 'NIKE DUNK', 'porcentaje', 29.00, 10, 0, '2026-05-19', 1, '2026-05-18 05:28:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_orden`
--

CREATE TABLE `detalle_orden` (
  `id` int(11) NOT NULL,
  `orden_id` int(11) NOT NULL,
  `variante_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_orden`
--

INSERT INTO `detalle_orden` (`id`, `orden_id`, `variante_id`, `cantidad`, `precio_unitario`, `subtotal`, `fecha_creacion`) VALUES
(4, 4, 4, 1, 110.00, 110.00, '2026-05-12 07:20:03'),
(5, 4, 5, 1, 80.00, 80.00, '2026-05-12 07:20:03'),
(6, 5, 5, 1, 62.00, 62.00, '2026-05-12 07:20:03'),
(25, 23, 4, 1, 110.00, 110.00, '2026-05-18 05:17:16'),
(26, 24, 16, 1, 100.00, 100.00, '2026-05-18 20:51:18'),
(27, 25, 2, 1, 80.00, 80.00, '2026-05-18 20:52:47'),
(28, 26, 4, 1, 110.00, 110.00, '2026-05-18 20:58:37'),
(29, 27, 4, 1, 110.00, 110.00, '2026-05-18 21:16:24'),
(30, 27, 2, 1, 80.00, 80.00, '2026-05-18 21:16:24'),
(31, 28, 17, 1, 60.00, 60.00, '2026-05-19 11:13:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direcciones`
--

CREATE TABLE `direcciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `ciudad` varchar(100) NOT NULL,
  `departamento` varchar(100) NOT NULL,
  `codigo_postal` varchar(20) DEFAULT NULL,
  `principal` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `direcciones`
--

INSERT INTO `direcciones` (`id`, `usuario_id`, `direccion`, `ciudad`, `departamento`, `codigo_postal`, `principal`) VALUES
(2, 6, 'Calle 10 # 45-78', 'Medell??n', 'Antioquia', '050001', 1),
(3, 7, 'Avenida 19 # 100-50', 'Cali', 'Valle del Cauca', '760001', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas`
--

CREATE TABLE `facturas` (
  `id` int(11) NOT NULL,
  `orden_id` int(11) NOT NULL,
  `numero_factura` varchar(50) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `facturas`
--

INSERT INTO `facturas` (`id`, `orden_id`, `numero_factura`, `fecha`) VALUES
(4, 4, 'FAC-20260511-00004', '2026-05-12 07:20:03'),
(5, 5, 'FAC-20260511-00005', '2026-05-12 07:20:03'),
(23, 23, 'FAC-20260518-00023', '2026-05-18 05:17:16'),
(24, 24, 'FAC-20260518-00024', '2026-05-18 20:51:18'),
(25, 25, 'FAC-20260518-00025', '2026-05-18 20:52:47'),
(26, 26, 'FAC-20260518-00026', '2026-05-18 20:58:37'),
(27, 27, 'FAC-20260518-00027', '2026-05-18 21:16:24'),
(28, 28, 'FAC-20260519-00028', '2026-05-19 11:13:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imagenes_productos`
--

CREATE TABLE `imagenes_productos` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `imagen` varchar(255) NOT NULL,
  `orden` int(11) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `imagenes_productos`
--

INSERT INTO `imagenes_productos` (`id`, `producto_id`, `imagen`, `orden`, `activo`) VALUES
(1, 9, 'prod_6a0b39f4782da.png', 0, 1),
(2, 9, 'prod_6a0b39f479065.png', 0, 1),
(3, 9, 'prod_6a0b39f479b1a.png', 0, 1),
(4, 10, 'prod_6a0b3af6f13f3.png', 0, 1),
(5, 10, 'prod_6a0b3af6f233b.png', 0, 1),
(7, 11, 'prod_6a0b3c5ace8ad.png', 0, 1),
(8, 11, 'prod_6a0b3c5acf7f5.png', 0, 1),
(9, 12, 'prod_6a0b42199d687.png', 0, 1),
(10, 12, 'prod_6a0b42199e2ab.png', 0, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_inventario`
--

CREATE TABLE `movimientos_inventario` (
  `id` int(11) NOT NULL,
  `variante_id` int(11) NOT NULL,
  `tipo` enum('entrada','salida') NOT NULL,
  `cantidad` int(11) NOT NULL,
  `motivo` varchar(150) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `movimientos_inventario`
--

INSERT INTO `movimientos_inventario` (`id`, `variante_id`, `tipo`, `cantidad`, `motivo`, `usuario_id`, `fecha`) VALUES
(1, 1, 'entrada', 20, 'Stock inicial de variante', NULL, '2026-05-12 07:20:03'),
(2, 2, 'entrada', 50, 'Stock inicial de variante', NULL, '2026-05-12 07:20:03'),
(3, 3, 'entrada', 10, 'Stock inicial de variante', NULL, '2026-05-12 07:20:03'),
(4, 4, 'entrada', 20, 'Stock inicial de variante', NULL, '2026-05-12 07:20:03'),
(5, 5, 'entrada', 30, 'Stock inicial de variante', NULL, '2026-05-12 07:20:03'),
(6, 4, 'salida', 4, 'Venta realizada', NULL, '2026-05-12 10:20:59'),
(7, 2, 'salida', 1, 'Venta realizada', NULL, '2026-05-12 10:20:59'),
(8, 4, 'salida', 1, 'Venta realizada', NULL, '2026-05-12 11:01:41'),
(9, 1, 'salida', 1, 'Venta realizada', NULL, '2026-05-12 11:19:40'),
(10, 2, 'salida', 1, 'Venta realizada', NULL, '2026-05-12 11:31:01'),
(11, 1, 'salida', 1, 'Venta realizada', NULL, '2026-05-12 11:31:01'),
(12, 4, 'salida', 2, 'Venta realizada', NULL, '2026-05-12 11:32:20'),
(13, 2, 'salida', 1, 'Venta realizada', NULL, '2026-05-12 11:33:26'),
(14, 2, 'salida', 1, 'Venta realizada', NULL, '2026-05-12 11:33:43'),
(15, 6, 'entrada', 5, 'Stock inicial de variante', NULL, '2026-05-12 11:37:51'),
(16, 2, 'salida', 5, 'Venta realizada', NULL, '2026-05-12 13:50:00'),
(17, 1, 'salida', 4, 'Venta realizada', NULL, '2026-05-12 13:54:57'),
(18, 1, 'salida', 13, 'Venta realizada', NULL, '2026-05-13 13:48:56'),
(19, 4, 'salida', 2, 'Venta realizada', NULL, '2026-05-13 21:28:35'),
(20, 4, 'salida', 1, 'Venta realizada', NULL, '2026-05-13 21:30:55'),
(21, 4, 'salida', 1, 'Venta realizada', NULL, '2026-05-13 21:31:39'),
(22, 4, 'salida', 1, 'Venta realizada', NULL, '2026-05-13 21:32:51'),
(23, 7, 'entrada', 10, 'Stock inicial de variante', NULL, '2026-05-18 05:27:55'),
(24, 8, 'entrada', 12, 'Stock inicial de variante', NULL, '2026-05-18 05:41:44'),
(25, 4, 'salida', 1, 'Venta realizada', 7, '2026-05-18 05:17:16'),
(26, 9, 'entrada', 10, 'Stock inicial de variante', NULL, '2026-05-18 21:16:06'),
(27, 9, 'entrada', 15, 'Ajuste por variante masiva', NULL, '2026-05-18 21:16:06'),
(28, 10, 'entrada', 10, 'Stock inicial de variante', NULL, '2026-05-18 21:16:06'),
(29, 11, 'entrada', 10, 'Stock inicial de variante', NULL, '2026-05-18 21:16:06'),
(30, 12, 'entrada', 5, 'Stock inicial de variante', NULL, '2026-05-18 21:16:06'),
(31, 13, 'entrada', 20, 'Stock inicial de variante', NULL, '2026-05-18 21:21:23'),
(32, 14, 'entrada', 10, 'Stock inicial de variante', NULL, '2026-05-18 21:21:23'),
(33, 15, 'entrada', 10, 'Stock inicial de variante', NULL, '2026-05-18 21:21:23'),
(34, 16, 'entrada', 10, 'Stock inicial de variante', NULL, '2026-05-18 21:21:23'),
(35, 17, 'entrada', 20, 'Stock inicial de variante', NULL, '2026-05-18 21:45:40'),
(36, 16, 'salida', 1, 'Venta realizada', 7, '2026-05-18 20:51:18'),
(37, 2, 'salida', 1, 'Venta realizada', 7, '2026-05-18 20:52:47'),
(38, 4, 'salida', 1, 'Venta realizada', 7, '2026-05-18 20:58:37'),
(39, 4, 'salida', 1, 'Venta realizada', 10, '2026-05-18 21:16:24'),
(40, 2, 'salida', 1, 'Venta realizada', 10, '2026-05-18 21:16:24'),
(41, 17, 'salida', 1, 'Venta realizada', 11, '2026-05-19 11:13:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordenes`
--

CREATE TABLE `ordenes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `cupon_id` int(11) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `descuento` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `estado` enum('pendiente','empacado','enviado','entregado','cancelado') DEFAULT 'pendiente',
  `direccion_envio` text DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ordenes`
--

INSERT INTO `ordenes` (`id`, `usuario_id`, `cupon_id`, `subtotal`, `descuento`, `total`, `estado`, `direccion_envio`, `fecha`) VALUES
(4, 6, NULL, 190.00, 0.00, 190.00, 'pendiente', 'Calle 10 # 45-78, Medell??n', '2026-05-12 07:20:03'),
(5, 7, NULL, 62.00, 0.00, 62.00, 'entregado', 'Avenida 19 # 100-50, Cali', '2026-05-12 07:20:03'),
(23, 7, NULL, 110.00, 0.00, 110.00, 'cancelado', 'carrera 5ta  #51-64', '2026-05-18 05:17:16'),
(24, 7, NULL, 100.00, 0.00, 100.00, 'pendiente', 'carrera5A #55-38', '2026-05-18 20:51:18'),
(25, 7, NULL, 80.00, 0.00, 80.00, 'pendiente', 'carrera 5ta #55-30', '2026-05-18 20:52:47'),
(26, 7, NULL, 110.00, 0.00, 110.00, 'pendiente', 'carrera 10 #50-30', '2026-05-18 20:58:37'),
(27, 10, NULL, 190.00, 0.00, 190.00, 'pendiente', 'carera 5A #49-31', '2026-05-18 21:16:24'),
(28, 11, NULL, 60.00, 0.00, 60.00, 'pendiente', 'Mañana le digo', '2026-05-19 11:13:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` int(11) NOT NULL,
  `orden_id` int(11) NOT NULL,
  `metodo` enum('efectivo','transferencia','tarjeta') NOT NULL,
  `estado` enum('pendiente','completado','rechazado') DEFAULT 'pendiente',
  `referencia` varchar(100) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `imagen_principal` varchar(255) DEFAULT NULL,
  `coleccion` enum('nueva_temporada','outlet','mas_vendidos','normal') DEFAULT 'normal',
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `descripcion`, `categoria_id`, `imagen_principal`, `coleccion`, `activo`, `fecha_creacion`) VALUES
(1, 'Camiseta Colombia visitante 2026', 'Camiseta de fútbol de la Selección Colombia en colores de visitante, con tecnología Climacool para un juego fresco y seco.', 1, 'prod_69d08daaf0a6b.png', 'nueva_temporada', 1, '2026-05-12 07:20:02'),
(2, 'Camiseta local alemania 2026', 'Camiseta de fútbol de ajuste ceñido con tecnología Climacool que mantiene el cuerpo fresco y seco.', 1, 'prod_69d1468675f8e.png', 'nueva_temporada', 1, '2026-05-12 07:20:02'),
(3, 'Nike Court Vision Low', 'El estilo Fastbreak del b??squetbol de los 80 y la cultura de ritmo r??pido del deporte actual se combinan en los Nike Court Vision Low.', 2, NULL, 'nueva_temporada', 0, '2026-05-12 07:20:02'),
(4, 'Nike Court Vision Low', 'El estilo Fastbreak del básquetbol de los 80 y la cultura de ritmo rápido del deporte actual se combinan en los Nike Court Vision Low.', 2, 'prod_69d14ba234d8b.png', 'nueva_temporada', 1, '2026-05-12 07:20:02'),
(5, 'SHORTS D4T WORKOUT 2 EN 1', 'Corte clásico con tecnología CLIMACOOL, tejido elástico de cuatro vías, material absorbente y de secado rápido.', 3, 'prod_69e6c01d61bdd.png', 'nueva_temporada', 0, '2026-05-12 07:20:02'),
(6, 'adidas algo', 'yfdchyfch', 4, NULL, 'normal', 0, '2026-05-12 11:37:29'),
(7, 'Nike Dunk Low', 'Creado para el parquet pero llevado a las calles, el icono del baloncesto de los 80 regresa con superposiciones perfectamente pulidas y colores clásicos de equipo. Con su icónico diseño de aros, la Nike Dunk Low reincorpora el vintage de los 80 a las calles, mientras que su cuello acolchado y escotado te permite llevar tu juego a cualquier parte, cómodamente.', 2, 'prod_6a0a169ee298c.png', 'normal', 1, '2026-05-18 05:27:26'),
(8, 'UA Unstoppable Fleece', 'UA Unstoppable está diseñado y probado con la misma atención al detalle por la que se conoce todo nuestro equipo. Nos aseguramos de que la tela siga siendo repelente al agua después de numerosos lavados, que la durabilidad se mantenga fuerte a través de la abrasión, que el contacto con la humedad se seque rápidamente y que la elasticidad sea la correcta al literalmente pesarla; hablamos de horas de pruebas únicas para ofrecerte lo mejor.', 3, 'prod_6a0a19e8a9378.png', 'nueva_temporada', 1, '2026-05-18 05:41:28'),
(9, 'camiseta de mexico local', 'Cuando el mundo se une para el mayor evento de fútbol, la camiseta de casa de México 26 se erige como símbolo de orgullo y unidad. Inspirado en el espíritu vibrante de los aficionados mexicanos, encarna la pasión y la lealtad que definen el amor de la nación por el juego.\r\n\r\nComo anfitriones de la Copa Mundial de la FIFA 26™, los mexicanos vistirán esta icónica camiseta con orgullo, cantando \"somos México\" mientras celebran con espíritu el patrimonio de su país.', 1, NULL, 'normal', 0, '2026-05-18 21:10:28'),
(10, 'camiseta mexico mujer 2026 local', 'Cuando el mundo se une para el mayor evento de fútbol, la camiseta de casa de México 26 se erige como símbolo de orgullo y unidad. Inspirado en el espíritu vibrante de los aficionados mexicanos, encarna la pasión y la lealtad que definen el amor de la nación por el juego.\r\n\r\nComo anfitriones de la Copa Mundial de la FIFA 26™, los mexicanos vistirán esta icónica camiseta con orgullo, cantando \"somos México\" mientras celebran con espíritu el patrimonio de su país.', 1, NULL, 'normal', 0, '2026-05-18 21:14:46'),
(11, 'camiseta mujer mexico 2026 local', 'Cuando el mundo se une para el mayor evento de fútbol, la camiseta de casa de México 26 se erige como símbolo de orgullo y unidad. Inspirado en el espíritu vibrante de los aficionados mexicanos, encarna la pasión y la lealtad que definen el amor de la nación por el juego.\r\n\r\nComo anfitriones de la Copa Mundial de la FIFA 26™, los mexicanos vistirán esta icónica camiseta con orgullo, cantando \"somos México\" mientras celebran con espíritu el patrimonio de su país.', 1, 'prod_6a0b3c5abd9ea.png', 'normal', 1, '2026-05-18 21:20:42'),
(12, 'Nike Brasilia 9.5', 'Toma tu equipo y ponte en marcha con la mochila Nike Brasilia. Tiene un montón de bolsillos para ayudarte a mantener la organización, como una funda que se adapta a tu laptop, bolsillos laterales de malla para botellas de agua y un bolsillo con cierre en la parte interior para mantener seguros los objetos pequeños', 4, 'prod_6a0b4219971b6.png', 'nueva_temporada', 1, '2026-05-18 21:45:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_roles`
--

CREATE TABLE `usuarios_roles` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `rol` enum('cliente','admin','inventario','contador','vendedor') DEFAULT 'cliente',
  `activo` tinyint(1) DEFAULT 1,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios_roles`
--

INSERT INTO `usuarios_roles` (`id`, `nombre`, `email`, `password`, `telefono`, `rol`, `activo`, `fecha_registro`) VALUES
(1, 'Administrador', 'admin@ecommerce.com', '$2y$10$RgHtK5z6Hei5a.45g2kh.ODK7angDQJDUqP06KSePzPVdq9he2jcO', '3001234567', 'admin', 1, '2026-05-12 07:20:03'),
(3, 'Mar??a Contador', 'maria@ecommerce.com', '$2y$10$9OLzEc3u6kv37XefWm8uVegeHjq3oa68eVBESsMo3i2HGtb1p0SIO', '3009876543', 'contador', 1, '2026-05-12 07:20:03'),
(4, 'Carlos Vendedor', 'carlos@ecommerce.com', '$2y$10$PzSh2tI.ZO6JGGanSNnSC.wY3t1e4GxRwQop3HKCDn1oh1TgQYe.K', '3005555555', 'vendedor', 1, '2026-05-12 07:20:03'),
(5, 'Luis Inventario', 'luis@ecommerce.com', '$2y$10$OqPe6ID7idne258G5L7U3.YsQZnLOHWYRB1.omcEvBPnnVvZGq9L.', '3004444444', 'inventario', 1, '2026-05-12 07:20:03'),
(6, 'Andrea Garc??a', 'andreagutierrez@hotmail.com', '$2y$10$mGnz8JOTwAVlDGpNTYv9suFZebYsAg/0kKyZvj5p6/tCneA/d7QvK', '302355789', 'cliente', 1, '2026-05-12 07:20:03'),
(7, 'Samuel Chaparro', 'samuelchaparro88@gmail.com', '$2y$10$v4GJ2bWDBLQgY7Info8VwesroEwelpMXZSRM675KqXNclxudKb7zS', '3152231327', 'cliente', 1, '2026-05-12 07:20:03'),
(10, 'andrea criales', 'nuryandrea@gmail.com', '$2y$10$vdSJxGWyAYmlv0.kPMiCZemmze4816eokUwqgr91GCZ0QLQb2BLRq', '3174153956', 'cliente', 1, '2026-05-18 21:09:41'),
(11, 'cristhian Ferley', 'cristian.toro@autogermana.com.co', '$2y$10$R2.pgdfrunhUNKNM50rTM.KNxBJRqH70GGHqcqVa27rfgV47IbH3W', '3107778637', 'cliente', 1, '2026-05-19 11:11:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `variantes`
--

CREATE TABLE `variantes` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `talla` varchar(10) NOT NULL,
  `color` varchar(50) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `variantes`
--

INSERT INTO `variantes` (`id`, `producto_id`, `talla`, `color`, `precio`, `stock`, `activo`) VALUES
(1, 1, 'XL', 'Azul', 359.00, 0, 1),
(2, 2, 'L', 'Blanco', 80.00, 39, 1),
(3, 3, '40', 'Negro', 110.00, 10, 1),
(4, 4, '40', 'Blanco', 110.00, 4, 1),
(5, 5, 'L', 'Negro', 62.00, 30, 1),
(6, 6, 'S', 'referencia visual', 50.00, 5, 1),
(7, 7, '40', 'referencia visual', 120.00, 10, 1),
(8, 8, 'L', 'referencia visual', 70.00, 12, 1),
(9, 10, 'S', 'imagen de referencia', 100.00, 25, 1),
(10, 10, 'M', 'imagen de referencia', 100.00, 10, 1),
(11, 10, 'L', 'imagen de referencia', 100.00, 10, 1),
(12, 10, 'XL', 'imagen de referencia', 100.00, 5, 1),
(13, 11, 'S', 'imagen de referencia', 100.00, 20, 1),
(14, 11, 'M', 'imagen de referencia', 100.00, 10, 1),
(15, 11, 'L', 'imagen de referencia', 100.00, 10, 1),
(16, 11, 'XL', 'imagen de referencia', 100.00, 9, 1),
(17, 12, 'UNICA', 'Maleta de entrenamiento (mediana, 24 L)', 60.00, 19, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `variante_id` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cupones`
--
ALTER TABLE `cupones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indices de la tabla `detalle_orden`
--
ALTER TABLE `detalle_orden`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orden_id` (`orden_id`),
  ADD KEY `variante_id` (`variante_id`);

--
-- Indices de la tabla `direcciones`
--
ALTER TABLE `direcciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orden_id` (`orden_id`),
  ADD UNIQUE KEY `numero_factura` (`numero_factura`);

--
-- Indices de la tabla `imagenes_productos`
--
ALTER TABLE `imagenes_productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_imagenes_producto` (`producto_id`);

--
-- Indices de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `idx_movimientos_variante` (`variante_id`);

--
-- Indices de la tabla `ordenes`
--
ALTER TABLE `ordenes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cupon_id` (`cupon_id`),
  ADD KEY `idx_ordenes_usuario` (`usuario_id`),
  ADD KEY `idx_ordenes_estado` (`estado`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orden_id` (`orden_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indices de la tabla `usuarios_roles`
--
ALTER TABLE `usuarios_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_rol` (`rol`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `variantes`
--
ALTER TABLE `variantes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_variantes_producto` (`producto_id`);

--
-- Indices de la tabla `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_wishlist` (`usuario_id`,`variante_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `variante_id` (`variante_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `cupones`
--
ALTER TABLE `cupones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `detalle_orden`
--
ALTER TABLE `detalle_orden`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `direcciones`
--
ALTER TABLE `direcciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `facturas`
--
ALTER TABLE `facturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `imagenes_productos`
--
ALTER TABLE `imagenes_productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT de la tabla `ordenes`
--
ALTER TABLE `ordenes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `usuarios_roles`
--
ALTER TABLE `usuarios_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `variantes`
--
ALTER TABLE `variantes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalle_orden`
--
ALTER TABLE `detalle_orden`
  ADD CONSTRAINT `detalle_orden_ibfk_1` FOREIGN KEY (`orden_id`) REFERENCES `ordenes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_orden_ibfk_2` FOREIGN KEY (`variante_id`) REFERENCES `variantes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `direcciones`
--
ALTER TABLE `direcciones`
  ADD CONSTRAINT `direcciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios_roles` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD CONSTRAINT `facturas_ibfk_1` FOREIGN KEY (`orden_id`) REFERENCES `ordenes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `imagenes_productos`
--
ALTER TABLE `imagenes_productos`
  ADD CONSTRAINT `imagenes_productos_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD CONSTRAINT `movimientos_inventario_ibfk_1` FOREIGN KEY (`variante_id`) REFERENCES `variantes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `movimientos_inventario_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios_roles` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `ordenes`
--
ALTER TABLE `ordenes`
  ADD CONSTRAINT `ordenes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios_roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ordenes_ibfk_2` FOREIGN KEY (`cupon_id`) REFERENCES `cupones` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`orden_id`) REFERENCES `ordenes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `variantes`
--
ALTER TABLE `variantes`
  ADD CONSTRAINT `variantes_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios_roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`variante_id`) REFERENCES `variantes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

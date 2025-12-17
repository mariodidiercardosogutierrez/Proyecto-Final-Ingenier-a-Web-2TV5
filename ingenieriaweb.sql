-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 17-12-2025 a las 17:35:03
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
-- Base de datos: `ingenieriaweb`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `album`
--

CREATE TABLE `album` (
  `id_album` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `anio` int(11) NOT NULL,
  `id_artista` int(11) NOT NULL,
  `duracion_total` int(11) DEFAULT 0,
  `descripcion_album` varchar(300) NOT NULL,
  `genero_album` varchar(100) NOT NULL,
  `cantidadtemas` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `album`
--

INSERT INTO `album` (`id_album`, `titulo`, `anio`, `id_artista`, `duracion_total`, `descripcion_album`, `genero_album`, `cantidadtemas`, `precio`) VALUES
(1, 'DTMF', 2025, 1, 0, 'Álbum de Bad Bunny', 'Reguetón', 8, 600.00),
(2, 'Cosa Nuestra', 2024, 2, 0, 'Álbum de Rauw Alejandro', 'Pop', 8, 600.00),
(3, 'Time Out', 2016, 3, 0, 'Álbum de Morat', 'Pop', 8, 600.00),
(4, 'Un Verano Sin Ti', 2022, 1, 0, 'Álbum de Bad Bunny', 'Urbano', 8, 600.00),
(5, 'Mis 40 en Bellas Artes', 2015, 4, 0, 'Álbum de Juan Gabriel', 'Balada', 8, 600.00),
(6, '20 Años', 1990, 5, 0, 'Álbum de Luis Miguel', 'Pop', 8, 600.00),
(7, 'The Razors Edge', 1990, 6, 0, 'Álbum de AC/DC', 'Rock', 8, 600.00),
(8, 'Eterno', 2021, 7, 0, 'Álbum de Manuel Medrano', 'Pop', 8, 600.00),
(9, 'emails i can’t send', 2023, 8, 0, 'Álbum de Sabrina Carpenter', 'Pop', 8, 600.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `albumcalificacion`
--

CREATE TABLE `albumcalificacion` (
  `id_album_calificacion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_album` int(11) NOT NULL,
  `calificacion` tinyint(4) NOT NULL,
  `fecha_calificacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `albumcalificacion`
--

INSERT INTO `albumcalificacion` (`id_album_calificacion`, `id_usuario`, `id_album`, `calificacion`, `fecha_calificacion`) VALUES
(1, 1, 1, 5, '2025-12-16 10:24:26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `albumfavorito`
--

CREATE TABLE `albumfavorito` (
  `id_album_favorito` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_album` int(11) NOT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `albumfavorito`
--

INSERT INTO `albumfavorito` (`id_album_favorito`, `id_usuario`, `id_album`, `fecha_registro`) VALUES
(2, 1, 7, '2025-12-14 07:09:45'),
(3, 1, 1, '2025-12-16 10:24:18');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `artista`
--

CREATE TABLE `artista` (
  `id_artista` int(11) NOT NULL,
  `nombre_artista` varchar(40) NOT NULL,
  `correo` varchar(50) DEFAULT NULL,
  `contrasenia` varchar(60) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `artista`
--

INSERT INTO `artista` (`id_artista`, `nombre_artista`, `correo`, `contrasenia`) VALUES
(1, 'Bad Bunny', 'badbunny@gmail.com', '123'),
(2, 'Rauw Alejandro', 'rarauw@gmail.com', '124'),
(3, 'Morat', 'Morat@gmail.com', '125'),
(4, 'Juan Gabriel', 'JuanG@gmail.com', '126'),
(5, 'Luis Miguel', 'LuisMiguel@gmail.com', '127'),
(6, 'AC/DC', 'ACDCOFICIAL@gmail.com', '128'),
(7, 'Manuel Medrano', 'ManuelM@gmail.com', '129'),
(8, 'Sabrina Carpenter', 'SabrinaC@gmail.com', '130');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cancion`
--

CREATE TABLE `cancion` (
  `id_cancion` int(11) NOT NULL,
  `titulo` varchar(50) NOT NULL,
  `duracion` time NOT NULL,
  `anio` int(11) NOT NULL,
  `fecha_subida` datetime NOT NULL,
  `genero` varchar(20) DEFAULT NULL,
  `precio` float NOT NULL,
  `id_artista` int(11) NOT NULL,
  `id_album` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cancion`
--

INSERT INTO `cancion` (`id_cancion`, `titulo`, `duracion`, `anio`, `fecha_subida`, `genero`, `precio`, `id_artista`, `id_album`) VALUES
(1, 'DtMF', '00:03:57', 2025, '2025-12-13 15:35:06', 'Regueton', 15, 1, 1),
(2, 'NuevaYol', '00:03:03', 2025, '2025-12-13 15:35:06', 'Regueton', 15, 1, 1),
(3, 'EoO', '00:03:24', 2025, '2025-12-13 15:35:06', 'Regueton', 15, 1, 1),
(4, 'WELTiTA', '00:03:07', 2025, '2025-12-13 15:35:06', 'Regueton', 15, 1, 1),
(5, 'VelDA', '00:03:54', 2025, '2025-12-13 15:35:06', 'Regueton', 15, 1, 1),
(6, 'EL CLuB', '00:03:41', 2025, '2025-12-13 15:35:06', 'Regueton', 15, 1, 1),
(7, 'BOKeTE', '00:03:35', 2025, '2025-12-13 15:35:06', 'Regueton', 15, 1, 1),
(8, 'TURiSTA', '00:03:09', 2025, '2025-12-13 15:35:06', 'Regueton', 15, 1, 1),
(9, 'Tu CON EL', '00:04:49', 2024, '2025-12-13 15:35:06', 'Pop', 14, 2, 2),
(10, '2:12 AM', '00:03:31', 2024, '2025-12-13 15:35:06', 'Pop', 14, 2, 2),
(11, 'Mil Mujeres', '00:02:48', 2024, '2025-12-13 15:35:06', 'Pop', 14, 2, 2),
(12, 'Cosa Nuestra', '00:04:20', 2024, '2025-12-13 15:35:06', 'Pop', 14, 2, 2),
(13, 'Que pasaria', '00:03:10', 2024, '2025-12-13 15:35:06', 'Pop', 14, 2, 2),
(14, 'Commited', '00:02:38', 2024, '2025-12-13 15:35:06', 'Pop', 14, 2, 2),
(15, 'Espresso Martini', '00:03:11', 2024, '2025-12-13 15:35:06', 'Pop', 14, 2, 2),
(16, 'Ni Me Conozco', '00:03:49', 2024, '2025-12-13 15:35:06', 'Pop', 14, 2, 2),
(17, 'Cuánto Me Duele', '00:03:15', 2016, '2025-12-13 15:35:06', 'Pop', 15, 3, 3),
(18, 'Yo Más Te Adoro', '00:03:51', 2016, '2025-12-13 15:35:06', 'Pop', 15, 3, 3),
(19, 'Sé Que Te Duele', '00:03:30', 2016, '2025-12-13 15:35:06', 'Pop', 15, 3, 3),
(20, 'Mi Nuevo Vicio', '00:03:23', 2016, '2025-12-13 15:35:06', 'Pop', 15, 3, 3),
(21, 'Cómo Te Atreves', '00:03:45', 2016, '2025-12-13 15:35:06', 'Pop', 15, 3, 3),
(22, 'Aprender a Quererte', '00:03:49', 2016, '2025-12-13 15:35:06', 'Pop', 15, 3, 3),
(23, 'En un Solo Día', '00:03:21', 2016, '2025-12-13 15:35:06', 'Pop', 15, 3, 3),
(24, 'La Última Vez', '00:03:33', 2016, '2025-12-13 15:35:06', 'Pop', 15, 3, 3),
(25, 'Moscow Mule', '00:03:47', 2022, '2025-12-13 15:35:06', 'Urbano', 16, 1, 4),
(26, 'Después de la Playa', '00:03:50', 2022, '2025-12-13 15:35:06', 'Urbano', 16, 1, 4),
(27, 'Me Porto Bonito', '00:02:58', 2022, '2025-12-13 15:35:06', 'Urbano', 16, 1, 4),
(28, 'Titi Me Preguntó', '00:04:02', 2022, '2025-12-13 15:35:06', 'Urbano', 16, 1, 4),
(29, 'Un Ratito', '00:02:55', 2022, '2025-12-13 15:35:06', 'Urbano', 16, 1, 4),
(30, 'Party', '00:03:47', 2022, '2025-12-13 15:35:06', 'Urbano', 16, 1, 4),
(31, 'Tarot', '00:03:57', 2022, '2025-12-13 15:35:06', 'Urbano', 16, 1, 4),
(32, 'Ojitos Lindos', '00:04:18', 2022, '2025-12-13 15:35:06', 'Urbano', 16, 1, 4),
(33, 'Así Fue', '00:05:11', 2014, '2025-12-13 15:35:06', 'Balada', 18, 4, 5),
(34, 'Querida', '00:04:40', 2014, '2025-12-13 15:35:06', 'Balada', 18, 4, 5),
(35, 'Amor Eterno', '00:06:42', 2014, '2025-12-13 15:35:06', 'Balada', 18, 4, 5),
(36, 'Hasta Que Te Conocí', '00:04:50', 2014, '2025-12-13 15:35:06', 'Balada', 18, 4, 5),
(37, 'Costumbres', '00:04:18', 2014, '2025-12-13 15:35:06', 'Balada', 18, 4, 5),
(38, 'Te Lo Pido Por Favor', '00:04:03', 2014, '2025-12-13 15:35:06', 'Balada', 18, 4, 5),
(39, 'Noa Noa', '00:04:12', 2014, '2025-12-13 15:35:06', 'Balada', 18, 4, 5),
(40, 'Se Me Olvidó Otra Vez', '00:03:30', 2014, '2025-12-13 15:35:06', 'Balada', 18, 4, 5),
(41, 'Soy Como Quiero Ser', '00:03:21', 1987, '2025-12-13 15:35:06', 'Pop', 12, 5, 6),
(42, 'Ahora Te Puedes Marchar', '00:03:13', 1987, '2025-12-13 15:35:06', 'Pop', 12, 5, 6),
(43, 'Eres Tú', '00:04:04', 1987, '2025-12-13 15:35:06', 'Pop', 12, 5, 6),
(44, 'Yo Que No Vivo Sin Ti', '00:03:26', 1987, '2025-12-13 15:35:06', 'Pop', 12, 5, 6),
(45, 'Rey de Corazones', '00:03:49', 1987, '2025-12-13 15:35:06', 'Pop', 12, 5, 6),
(46, 'Jimmy Jimmy', '00:03:48', 1987, '2025-12-13 15:35:06', 'Pop', 12, 5, 6),
(47, 'Tú Me Quemas', '00:03:35', 1987, '2025-12-13 15:35:06', 'Pop', 12, 5, 6),
(48, 'Es Mejor', '00:03:30', 1987, '2025-12-13 15:35:06', 'Pop', 12, 5, 6),
(49, 'Thunderstruck', '00:04:52', 1990, '2025-12-13 15:35:06', 'Rock', 17, 6, 7),
(50, 'Fire Your Guns', '00:02:54', 1990, '2025-12-13 15:35:06', 'Rock', 17, 6, 7),
(51, 'Moneytalks', '00:03:46', 1990, '2025-12-13 15:35:06', 'Rock', 17, 6, 7),
(52, 'The Razors Edge', '00:04:22', 1990, '2025-12-13 15:35:06', 'Rock', 17, 6, 7),
(53, 'Mistress for Christmas', '00:03:59', 1990, '2025-12-13 15:35:06', 'Rock', 17, 6, 7),
(54, 'Rock Your Heart Out', '00:04:07', 1990, '2025-12-13 15:35:06', 'Rock', 17, 6, 7),
(55, 'Are You Ready', '00:04:10', 1990, '2025-12-13 15:35:06', 'Rock', 17, 6, 7),
(56, 'Got You by the Balls', '00:04:28', 1990, '2025-12-13 15:35:06', 'Rock', 17, 6, 7),
(57, 'La Distancia', '00:03:55', 2021, '2025-12-13 15:35:06', 'Pop', 14, 7, 8),
(58, 'Nenita', '00:03:12', 2021, '2025-12-13 15:35:06', 'Pop', 14, 7, 8),
(59, 'Café', '00:03:18', 2021, '2025-12-13 15:35:06', 'Pop', 14, 7, 8),
(60, 'Este Cuento', '00:03:42', 2021, '2025-12-13 15:35:06', 'Pop', 14, 7, 8),
(61, 'Fin de Semana', '00:02:59', 2021, '2025-12-13 15:35:06', 'Pop', 14, 7, 8),
(62, 'Dime Qué Hago', '00:03:26', 2021, '2025-12-13 15:35:06', 'Pop', 14, 7, 8),
(63, 'Prende la Luz', '00:03:01', 2021, '2025-12-13 15:35:06', 'Pop', 14, 7, 8),
(64, 'Mi Otra Mitad', '00:03:47', 2021, '2025-12-13 15:35:06', 'Pop', 14, 7, 8),
(65, 'emails i cant send', '00:02:27', 2023, '2025-12-13 15:35:06', 'Pop', 16, 8, 9),
(66, 'Vicious', '00:02:56', 2023, '2025-12-13 15:35:06', 'Pop', 16, 8, 9),
(67, 'Read Your Mind', '00:02:38', 2023, '2025-12-13 15:35:06', 'Pop', 16, 8, 9),
(68, 'because i liked a boy', '00:03:18', 2023, '2025-12-13 15:35:06', 'Pop', 16, 8, 9),
(69, 'Already Over', '00:02:20', 2023, '2025-12-13 15:35:06', 'Pop', 16, 8, 9),
(70, 'Skinny Dipping', '00:03:04', 2023, '2025-12-13 15:35:06', 'Pop', 16, 8, 9),
(71, 'Fast Times', '00:02:56', 2023, '2025-12-13 15:35:06', 'Pop', 16, 8, 9),
(72, 'Nonsense', '00:02:43', 2023, '2025-12-13 15:35:06', 'Pop', 16, 8, 9);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito`
--

CREATE TABLE `carrito` (
  `id_carrito` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `carrito`
--

INSERT INTO `carrito` (`id_carrito`, `id_usuario`) VALUES
(5, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carritoitem`
--

CREATE TABLE `carritoitem` (
  `id_carritoItem` int(11) NOT NULL,
  `id_carrito` int(11) NOT NULL,
  `tipo` varchar(10) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio` float NOT NULL,
  `fecha_agregado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compra`
--

CREATE TABLE `compra` (
  `id_compra` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha` datetime NOT NULL,
  `total` float NOT NULL,
  `id_tarjeta` int(11) NOT NULL,
  `id_estatus` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `compra`
--

INSERT INTO `compra` (`id_compra`, `id_usuario`, `fecha`, `total`, `id_tarjeta`, `id_estatus`) VALUES
(1, 1, '2025-12-13 15:51:19', 600, 2, 4),
(2, 1, '2025-12-13 16:37:40', 600, 4, 4),
(3, 1, '2025-12-13 16:52:59', 600, 2, 4),
(4, 1, '2025-12-13 21:42:16', 600, 2, 4),
(5, 1, '2025-12-16 10:28:05', 1800, 2, 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compraalbum`
--

CREATE TABLE `compraalbum` (
  `id_compra_album` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_album` int(11) NOT NULL,
  `id_tarjeta` int(11) NOT NULL,
  `fecha_compra` datetime DEFAULT current_timestamp(),
  `precio` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compraitem`
--

CREATE TABLE `compraitem` (
  `id_compraItem` int(11) NOT NULL,
  `id_compra` int(11) NOT NULL,
  `tipo` varchar(10) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `compraitem`
--

INSERT INTO `compraitem` (`id_compraItem`, `id_compra`, `tipo`, `id_producto`, `cantidad`, `precio`) VALUES
(1, 1, 'album', 7, 1, 600),
(2, 2, 'album', 4, 1, 600),
(3, 3, 'album', 1, 1, 600),
(4, 4, 'album', 7, 1, 600),
(5, 5, 'album', 8, 1, 600),
(6, 5, 'album', 6, 1, 600),
(7, 5, 'album', 2, 1, 600);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estatus_compra`
--

CREATE TABLE `estatus_compra` (
  `id_estatus` int(11) NOT NULL,
  `estatus` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estatus_compra`
--

INSERT INTO `estatus_compra` (`id_estatus`, `estatus`) VALUES
(1, 'Pendiente'),
(2, 'Pagado'),
(3, 'Cancelado'),
(4, 'Completado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarjeta`
--

CREATE TABLE `tarjeta` (
  `id_tarjeta` int(11) NOT NULL,
  `nombre_titular` varchar(60) NOT NULL,
  `numero_tarjeta` varchar(20) NOT NULL,
  `mes_exp` varchar(2) NOT NULL,
  `anio_exp` varchar(4) NOT NULL,
  `cvv` varchar(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tarjeta`
--

INSERT INTO `tarjeta` (`id_tarjeta`, `nombre_titular`, `numero_tarjeta`, `mes_exp`, `anio_exp`, `cvv`) VALUES
(1, 'Mauricio Ramos', '4111111111111111', '08', '2029', '123'),
(2, 'Mauricio Ramos', '4111111111111112', '08', '2029', '123'),
(3, 'Mario', '1111111111111', '10', '2033', '222'),
(4, 'mar', '111111111111111', '11', '2031', '222'),
(5, 'Mario C', '4111111111111111', '10', '2034', '123');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefono` varchar(10) NOT NULL,
  `fecha_nac` date NOT NULL,
  `password` varchar(100) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `email`, `telefono`, `fecha_nac`, `password`, `role`, `created_at`, `last_login`) VALUES
(1, 'Mario', 'Cardoso', 'mariocardoso@gmail.com', '5527432501', '2025-12-03', '1', 'admin', '2025-12-13 21:49:59', '2025-12-17 09:52:11'),
(2, 'Luna', 'A', 'gusa@gmail.com', '5511123231', '2000-02-04', '123456789Qq:', 'user', '2025-12-17 15:56:59', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_tarjeta`
--

CREATE TABLE `usuario_tarjeta` (
  `id_usuario_tarjeta` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_tarjeta` int(11) NOT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario_tarjeta`
--

INSERT INTO `usuario_tarjeta` (`id_usuario_tarjeta`, `id_usuario`, `id_tarjeta`, `fecha_registro`) VALUES
(1, 1, 2, '2025-12-13 15:51:03');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `album`
--
ALTER TABLE `album`
  ADD PRIMARY KEY (`id_album`),
  ADD KEY `id_artista` (`id_artista`);

--
-- Indices de la tabla `albumcalificacion`
--
ALTER TABLE `albumcalificacion`
  ADD PRIMARY KEY (`id_album_calificacion`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`,`id_album`),
  ADD KEY `id_album` (`id_album`);

--
-- Indices de la tabla `albumfavorito`
--
ALTER TABLE `albumfavorito`
  ADD PRIMARY KEY (`id_album_favorito`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`,`id_album`),
  ADD KEY `id_album` (`id_album`);

--
-- Indices de la tabla `artista`
--
ALTER TABLE `artista`
  ADD PRIMARY KEY (`id_artista`);

--
-- Indices de la tabla `cancion`
--
ALTER TABLE `cancion`
  ADD PRIMARY KEY (`id_cancion`),
  ADD KEY `id_artista` (`id_artista`),
  ADD KEY `id_album` (`id_album`);

--
-- Indices de la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD PRIMARY KEY (`id_carrito`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `carritoitem`
--
ALTER TABLE `carritoitem`
  ADD PRIMARY KEY (`id_carritoItem`),
  ADD KEY `idx_carrito` (`id_carrito`);

--
-- Indices de la tabla `compra`
--
ALTER TABLE `compra`
  ADD PRIMARY KEY (`id_compra`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_tarjeta` (`id_tarjeta`),
  ADD KEY `id_estatus` (`id_estatus`);

--
-- Indices de la tabla `compraalbum`
--
ALTER TABLE `compraalbum`
  ADD PRIMARY KEY (`id_compra_album`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_album` (`id_album`),
  ADD KEY `id_tarjeta` (`id_tarjeta`);

--
-- Indices de la tabla `compraitem`
--
ALTER TABLE `compraitem`
  ADD PRIMARY KEY (`id_compraItem`),
  ADD KEY `id_compra` (`id_compra`);

--
-- Indices de la tabla `estatus_compra`
--
ALTER TABLE `estatus_compra`
  ADD PRIMARY KEY (`id_estatus`);

--
-- Indices de la tabla `tarjeta`
--
ALTER TABLE `tarjeta`
  ADD PRIMARY KEY (`id_tarjeta`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `usuario_tarjeta`
--
ALTER TABLE `usuario_tarjeta`
  ADD PRIMARY KEY (`id_usuario_tarjeta`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`,`id_tarjeta`),
  ADD KEY `id_tarjeta` (`id_tarjeta`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `album`
--
ALTER TABLE `album`
  MODIFY `id_album` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `albumcalificacion`
--
ALTER TABLE `albumcalificacion`
  MODIFY `id_album_calificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `albumfavorito`
--
ALTER TABLE `albumfavorito`
  MODIFY `id_album_favorito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `artista`
--
ALTER TABLE `artista`
  MODIFY `id_artista` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `cancion`
--
ALTER TABLE `cancion`
  MODIFY `id_cancion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT de la tabla `carrito`
--
ALTER TABLE `carrito`
  MODIFY `id_carrito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `carritoitem`
--
ALTER TABLE `carritoitem`
  MODIFY `id_carritoItem` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `compra`
--
ALTER TABLE `compra`
  MODIFY `id_compra` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `compraalbum`
--
ALTER TABLE `compraalbum`
  MODIFY `id_compra_album` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `compraitem`
--
ALTER TABLE `compraitem`
  MODIFY `id_compraItem` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `estatus_compra`
--
ALTER TABLE `estatus_compra`
  MODIFY `id_estatus` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tarjeta`
--
ALTER TABLE `tarjeta`
  MODIFY `id_tarjeta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuario_tarjeta`
--
ALTER TABLE `usuario_tarjeta`
  MODIFY `id_usuario_tarjeta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `album`
--
ALTER TABLE `album`
  ADD CONSTRAINT `album_ibfk_1` FOREIGN KEY (`id_artista`) REFERENCES `artista` (`id_artista`);

--
-- Filtros para la tabla `albumcalificacion`
--
ALTER TABLE `albumcalificacion`
  ADD CONSTRAINT `albumcalificacion_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `albumcalificacion_ibfk_2` FOREIGN KEY (`id_album`) REFERENCES `album` (`id_album`);

--
-- Filtros para la tabla `albumfavorito`
--
ALTER TABLE `albumfavorito`
  ADD CONSTRAINT `albumfavorito_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `albumfavorito_ibfk_2` FOREIGN KEY (`id_album`) REFERENCES `album` (`id_album`);

--
-- Filtros para la tabla `cancion`
--
ALTER TABLE `cancion`
  ADD CONSTRAINT `cancion_ibfk_1` FOREIGN KEY (`id_artista`) REFERENCES `artista` (`id_artista`),
  ADD CONSTRAINT `cancion_ibfk_2` FOREIGN KEY (`id_album`) REFERENCES `album` (`id_album`);

--
-- Filtros para la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD CONSTRAINT `carrito_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `carritoitem`
--
ALTER TABLE `carritoitem`
  ADD CONSTRAINT `carritoitem_ibfk_1` FOREIGN KEY (`id_carrito`) REFERENCES `carrito` (`id_carrito`);

--
-- Filtros para la tabla `compra`
--
ALTER TABLE `compra`
  ADD CONSTRAINT `compra_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `compra_ibfk_2` FOREIGN KEY (`id_tarjeta`) REFERENCES `tarjeta` (`id_tarjeta`),
  ADD CONSTRAINT `compra_ibfk_3` FOREIGN KEY (`id_estatus`) REFERENCES `estatus_compra` (`id_estatus`);

--
-- Filtros para la tabla `compraalbum`
--
ALTER TABLE `compraalbum`
  ADD CONSTRAINT `compraalbum_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `compraalbum_ibfk_2` FOREIGN KEY (`id_album`) REFERENCES `album` (`id_album`),
  ADD CONSTRAINT `compraalbum_ibfk_3` FOREIGN KEY (`id_tarjeta`) REFERENCES `tarjeta` (`id_tarjeta`);

--
-- Filtros para la tabla `compraitem`
--
ALTER TABLE `compraitem`
  ADD CONSTRAINT `compraitem_ibfk_1` FOREIGN KEY (`id_compra`) REFERENCES `compra` (`id_compra`);

--
-- Filtros para la tabla `usuario_tarjeta`
--
ALTER TABLE `usuario_tarjeta`
  ADD CONSTRAINT `usuario_tarjeta_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `usuario_tarjeta_ibfk_2` FOREIGN KEY (`id_tarjeta`) REFERENCES `tarjeta` (`id_tarjeta`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

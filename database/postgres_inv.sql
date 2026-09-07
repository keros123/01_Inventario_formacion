-- PostgreSQL Dump
-- Convertido desde MySQL/MariaDB
-- Fecha: 07-09-2026

BEGIN;

--
-- Base de datos: inventariob105
-- Nota: En PostgreSQL, CREATE DATABASE debe ejecutarse fuera de una transacción.
-- Si la base de datos no existe, ejecútela aparte:
--   CREATE DATABASE inventariob105 WITH ENCODING 'UTF8' LC_COLLATE = 'en_US.UTF-8' LC_CTYPE = 'en_US.UTF-8' TEMPLATE template0;
-- Luego conectese con: \c inventariob105
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla inv_form_cuentadantes
--

CREATE TABLE inv_form_cuentadantes (
  "Cedula" varchar(20) NOT NULL,
  "Nombres" varchar(150) NOT NULL,
  "Estado" text NOT NULL DEFAULT 'Activo' CHECK ("Estado" IN ('Activo','Inactivo'))
);

--
-- Volcado de datos para la tabla inv_form_cuentadantes
--

INSERT INTO inv_form_cuentadantes ("Cedula", "Nombres", "Estado") VALUES
('1068657448', 'José Fernando Sánchez Bruno', 'Activo'),
('10765884', 'Tonny López', 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla inv_form_det_movimientos
--

CREATE TABLE inv_form_det_movimientos (
  "Consecutivo" integer NOT NULL GENERATED ALWAYS AS IDENTITY,
  "id_movimiento" integer NOT NULL,
  "id_Detalle" integer NOT NULL DEFAULT 1,
  "Codigo_elemento" varchar(50) NOT NULL,
  "Cantidad" integer NOT NULL DEFAULT 1,
  "Estado" text NOT NULL DEFAULT 'Activo' CHECK ("Estado" IN ('Activo','Inactivo'))
);

--
-- Volcado de datos para la tabla inv_form_det_movimientos
--

INSERT INTO inv_form_det_movimientos ("Consecutivo", "id_movimiento", "id_Detalle", "Codigo_elemento", "Cantidad", "Estado") OVERRIDING SYSTEM VALUE VALUES
(17, 9, 1, '100001', 16, 'Activo'),
(18, 10, 1, '100002', 6, 'Activo'),
(19, 11, 1, '100002', 1, 'Activo'),
(20, 12, 1, '100003', 3, 'Activo'),
(21, 13, 1, '100004', 7, 'Activo'),
(22, 14, 1, '100004', 1, 'Activo'),
(23, 15, 1, '100005', 7, 'Activo'),
(24, 16, 1, '100006', 3, 'Activo'),
(25, 17, 1, '100007', 2, 'Activo'),
(26, 18, 1, '100007', 1, 'Activo'),
(27, 19, 1, '100008', 2, 'Activo'),
(28, 20, 1, '100009', 2, 'Activo'),
(29, 21, 1, '1000010', 3, 'Activo'),
(30, 22, 1, '1000010', 1, 'Activo'),
(31, 23, 1, '1000011', 2, 'Activo'),
(32, 24, 1, '1000012', 3, 'Activo'),
(33, 25, 1, '1000012', 1, 'Activo'),
(34, 26, 1, '1000013', 4, 'Activo'),
(35, 27, 1, '1000014', 3, 'Activo'),
(36, 28, 1, '1000015', 2, 'Activo'),
(37, 29, 1, '1000016', 2, 'Activo'),
(38, 30, 1, '1000017', 22, 'Activo'),
(39, 31, 1, '1000017', 4, 'Activo'),
(40, 32, 1, '1000018', 9, 'Activo'),
(41, 33, 1, '1000019', 10, 'Activo'),
(42, 34, 1, '1000019', 5, 'Activo'),
(43, 35, 1, '1000021', 4, 'Activo'),
(44, 36, 1, '1000021', 1, 'Activo'),
(45, 37, 1, '1000020', 10, 'Activo'),
(46, 38, 1, '1000020', 3, 'Activo'),
(47, 39, 1, '1000022', 5, 'Activo'),
(48, 40, 1, '1000023', 2, 'Activo'),
(49, 41, 1, '1000024', 3, 'Activo'),
(50, 42, 1, '1000025', 1, 'Activo'),
(51, 43, 1, '1000026', 7, 'Activo'),
(52, 44, 1, '1000027', 4, 'Activo'),
(53, 45, 1, '1000027', 1, 'Activo'),
(54, 46, 1, '1000028', 3, 'Activo'),
(55, 47, 1, '1000029', 2, 'Activo'),
(56, 48, 1, '1000030', 1, 'Activo'),
(57, 49, 1, '1000031', 4, 'Activo'),
(58, 50, 1, '1000032', 1, 'Activo'),
(59, 51, 1, '1000032', 1, 'Activo'),
(60, 52, 1, '10000333', 1, 'Activo'),
(61, 53, 1, '1000034', 1, 'Activo'),
(62, 54, 1, '1000035', 1, 'Activo'),
(63, 55, 1, '1000036', 1, 'Activo'),
(64, 56, 1, '1000038', 4, 'Activo'),
(65, 57, 1, '1000037', 2, 'Activo'),
(66, 58, 1, '1000037', 1, 'Activo');

-- Reajustar secuencia de identidad
SELECT setval(pg_get_serial_sequence('inv_form_det_movimientos', 'Consecutivo'), 67, false);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla inv_form_inventario
--

CREATE TABLE inv_form_inventario (
  "Codigo" varchar(50) NOT NULL,
  "Elemento" varchar(150) NOT NULL,
  "Consumible" text NOT NULL DEFAULT 'no' CHECK ("Consumible" IN ('si','no')),
  "Descripcion" text DEFAULT NULL,
  "Cantidad" integer NOT NULL DEFAULT 0,
  "Fotografia" varchar(255) DEFAULT NULL,
  "Estado" text NOT NULL DEFAULT 'Activo' CHECK ("Estado" IN ('Activo','Inactivo'))
);

--
-- Volcado de datos para la tabla inv_form_inventario
--

INSERT INTO inv_form_inventario ("Codigo", "Elemento", "Consumible", "Descripcion", "Cantidad", "Fotografia", "Estado") VALUES
('100001', 'Pelador de cable QPCOM 501-B', 'si', 'Materiales de formación', 16, 'uploads/inventario/100001.jpeg', 'Activo'),
('1000010', 'Destornillador de pala Stanley 4"', 'no', '', 2, NULL, 'Activo'),
('1000011', 'Destornillador de estrella Stanley 10"', 'no', '', 2, NULL, 'Activo'),
('1000012', 'Pelador de cable network tool', 'no', '', 2, NULL, 'Activo'),
('1000013', 'Ponchadora de cable coaxial', 'si', '', 4, NULL, 'Activo'),
('1000014', 'Alicate pico loro 12"', 'no', '', 3, NULL, 'Activo'),
('1000015', 'Ponchadora de impacto Qpcom 110', 'si', '', 2, NULL, 'Activo'),
('1000016', 'Ponchadora de impacto Trednet', 'si', '', 2, NULL, 'Activo'),
('1000017', 'Patch Cord UTP Cat 6 Azul 3m', 'si', '', 18, NULL, 'Activo'),
('1000018', 'Adaptador serial a usb', 'si', '', 9, NULL, 'Activo'),
('1000019', 'Cable de consola rj45 a usb', 'si', '', 5, NULL, 'Activo'),
('100002', 'Stripper Fiber Optic', 'si', '', 5, NULL, 'Activo'),
('1000020', 'Cable serial sencillo', 'si', '', 7, NULL, 'Activo'),
('1000021', 'Cable serial DCE - DTE', 'si', '', 3, NULL, 'Activo'),
('1000022', 'Cable de consola rj45 a serial', 'no', '', 5, NULL, 'Activo'),
('1000023', 'Modulo Cisco EHWIC-4ESG', 'no', '', 2, NULL, 'Activo'),
('1000024', 'Transceiver mc220l', 'no', '', 3, NULL, 'Activo'),
('1000025', 'Ponchadora commscope cat 6', 'no', '', 1, NULL, 'Activo'),
('1000026', 'Pelador de chaqueta de fibra longitudinala Ftth', 'no', '', 7, NULL, 'Activo'),
('1000027', 'Cizalla napoleon 14"', 'no', '', 3, NULL, 'Activo'),
('1000028', 'Cortadora de fibra Fc-6s', 'no', '', 3, NULL, 'Activo'),
('1000029', 'Antena Ubiquiti M5 5ghz', 'no', '', 2, NULL, 'Activo'),
('100003', 'Tijera para Kevlar', 'si', '', 3, NULL, 'Activo'),
('1000030', 'Disco duro externo 4t Toshiba', 'no', '', 1, NULL, 'Activo'),
('1000031', 'Fuente 12v 5 amp', 'no', '', 4, NULL, 'Activo'),
('1000032', 'Camara ip imou cruizer 2 3k', 'si', '', 0, NULL, 'Activo'),
('10000333', 'Disco duro dvr 4t Purple', 'si', '', 1, NULL, 'Activo'),
('1000034', 'Cámara Térmica Dhua', 'no', '', 1, NULL, 'Activo'),
('1000035', 'Impresora Brother L800', 'si', '', 1, NULL, 'Activo'),
('1000036', 'Nvr Dahua 4k ip', 'no', '', 1, NULL, 'Activo'),
('1000037', 'Tester probador de red cable utp  Trednet', 'no', '', 1, NULL, 'Activo'),
('1000038', 'Resma de papel Tamaño Carta', 'si', '', 4, NULL, 'Activo'),
('100004', 'Cortadora de precisión', 'si', '', 6, NULL, 'Activo'),
('100005', 'Stripper Cable Coaxial', 'si', '', 7, NULL, 'Activo'),
('100006', 'Punta para trazar fibra', 'si', '', 3, NULL, 'Activo'),
('100007', 'Pinza Stanley 8"', 'si', '', 1, NULL, 'Activo'),
('100008', 'Pinza Toolcraft 9"', 'si', '', 2, NULL, 'Activo'),
('100009', 'Alicate pela cable Stanley', 'si', '', 2, NULL, 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla inv_form_movimientos
--

CREATE TABLE inv_form_movimientos (
  "id_movimiento" integer NOT NULL GENERATED ALWAYS AS IDENTITY,
  "Tipo" text NOT NULL CHECK ("Tipo" IN ('Ingreso','Prestamo','Devolucion')),
  "id_cuentadante" integer NOT NULL,
  "Fecha" date NOT NULL,
  "Cedula_cuentadante" varchar(20) DEFAULT NULL,
  "Uso" text NOT NULL CHECK ("Uso" IN ('Formacion','TIC')),
  "Ficha" varchar(50) DEFAULT NULL,
  "Descripcion" text DEFAULT NULL,
  "Estado" text NOT NULL DEFAULT 'Activo' CHECK ("Estado" IN ('Activo','Inactivo','Cerrado')),
  "id_movimiento_ref" integer DEFAULT NULL
);

--
-- Volcado de datos para la tabla inv_form_movimientos
--

INSERT INTO inv_form_movimientos ("id_movimiento", "Tipo", "id_cuentadante", "Fecha", "Cedula_cuentadante", "Uso", "Ficha", "Descripcion", "Estado", "id_movimiento_ref") OVERRIDING SYSTEM VALUE VALUES
(9, 'Ingreso', 0, '2026-06-09', '1068657448', 'Formacion', NULL, '', 'Activo', NULL),
(10, 'Ingreso', 0, '2026-09-02', '1068657448', 'Formacion', NULL, '', 'Activo', NULL),
(11, 'Prestamo', 0, '2026-09-02', '1068657448', 'Formacion', '3535704', 'Tonny Lopez guardado en looker herramienas WS', 'Activo', NULL),
(12, 'Ingreso', 0, '2026-09-02', '1068657448', 'Formacion', NULL, '', 'Activo', NULL),
(13, 'Ingreso', 0, '2026-09-02', '1068657448', 'Formacion', NULL, '', 'Activo', NULL),
(14, 'Prestamo', 0, '2026-09-02', '1068657448', 'Formacion', '3535704', 'Tonny López guardado en looker de herrmientas WS', 'Activo', NULL),
(15, 'Ingreso', 0, '2026-09-02', '1068657448', 'Formacion', NULL, '', 'Activo', NULL),
(16, 'Ingreso', 0, '2026-09-02', '1068657448', 'Formacion', NULL, '', 'Activo', NULL),
(17, 'Ingreso', 0, '2026-09-02', '1068657448', 'Formacion', NULL, '', 'Activo', NULL),
(18, 'Prestamo', 0, '2026-09-02', '1068657448', 'Formacion', '3535704', 'Tonny guardada en la vitrina de madera', 'Activo', NULL),
(19, 'Ingreso', 0, '2026-09-02', '1068657448', 'Formacion', NULL, '', 'Activo', NULL),
(20, 'Ingreso', 0, '2026-09-02', '1068657448', 'Formacion', NULL, '', 'Activo', NULL),
(21, 'Ingreso', 0, '2026-09-02', '1068657448', 'Formacion', NULL, '', 'Activo', NULL),
(22, 'Prestamo', 0, '2026-09-02', '1068657448', 'Formacion', '3535704', 'Tonny guardado en la vitrina de madera', 'Activo', NULL),
(23, 'Ingreso', 0, '2026-09-02', '1068657448', 'Formacion', NULL, '', 'Activo', NULL),
(24, 'Ingreso', 0, '2026-09-02', '1068657448', 'Formacion', NULL, '', 'Activo', NULL),
(25, 'Prestamo', 0, '2026-09-02', '1068657448', 'Formacion', '3535704', 'Tonny guardado en el looker de herramientas WS', 'Activo', NULL),
(26, 'Ingreso', 0, '2026-09-02', '1068657448', 'Formacion', NULL, '', 'Activo', NULL),
(27, 'Ingreso', 0, '2026-09-02', '1068657448', 'Formacion', NULL, '', 'Activo', NULL),
(28, 'Ingreso', 0, '2026-09-02', '1068657448', 'Formacion', NULL, '', 'Activo', NULL),
(29, 'Ingreso', 0, '2026-09-02', '1068657448', 'Formacion', NULL, '', 'Activo', NULL),
(30, 'Ingreso', 0, '2026-09-02', '1068657448', 'Formacion', NULL, '', 'Activo', NULL),
(31, 'Prestamo', 0, '2026-09-02', '1068657448', 'Formacion', '3535704', 'Tonny de uso en formación', 'Activo', NULL),
(32, 'Ingreso', 0, '2026-09-02', '1068657448', 'Formacion', NULL, '', 'Activo', NULL),
(33, 'Ingreso', 0, '2026-09-02', '1068657448', 'Formacion', NULL, '', 'Activo', NULL),
(34, 'Prestamo', 0, '2026-09-02', '1068657448', 'Formacion', '3535704', 'Tonny de uso para la formación', 'Activo', NULL),
(35, 'Ingreso', 0, '2026-09-02', '1068657448', 'Formacion', NULL, '', 'Activo', NULL),
(36, 'Prestamo', 0, '2026-09-02', '1068657448', 'Formacion', '3535704', 'Tonny guardado en vitrina de madera', 'Activo', NULL),
(37, 'Ingreso', 0, '2026-09-02', '1068657448', 'Formacion', NULL, '', 'Activo', NULL),
(38, 'Prestamo', 0, '2026-09-02', '1068657448', 'Formacion', '3535704', 'Tonny de uso para la formación', 'Activo', NULL),
(39, 'Ingreso', 0, '2026-09-02', '1068657448', 'Formacion', NULL, '', 'Activo', NULL),
(40, 'Ingreso', 0, '2026-09-02', '1068657448', 'Formacion', NULL, '', 'Activo', NULL),
(41, 'Ingreso', 0, '2026-09-02', '1068657448', 'Formacion', NULL, '', 'Activo', NULL),
(42, 'Ingreso', 0, '2026-09-02', '1068657448', 'Formacion', NULL, '', 'Activo', NULL),
(43, 'Ingreso', 0, '2026-09-02', '1068657448', 'Formacion', NULL, '', 'Activo', NULL),
(44, 'Ingreso', 0, '2026-09-02', '1068657448', 'Formacion', NULL, '', 'Activo', NULL),
(45, 'Prestamo', 0, '2026-09-02', '1068657448', 'Formacion', '3535704', 'Tonny guardada en el looker de herramientas WS', 'Activo', NULL),
(46, 'Ingreso', 0, '2026-09-02', '1068657448', 'Formacion', NULL, '', 'Activo', NULL),
(47, 'Ingreso', 0, '2026-09-02', '1068657448', 'Formacion', NULL, '', 'Activo', NULL),
(48, 'Ingreso', 0, '2026-09-02', '1068657448', 'Formacion', NULL, '', 'Activo', NULL),
(49, 'Ingreso', 0, '2026-09-02', '1068657448', 'Formacion', NULL, '', 'Activo', NULL),
(50, 'Ingreso', 0, '2026-09-02', '1068657448', 'Formacion', NULL, '', 'Activo', NULL),
(51, 'Prestamo', 0, '2026-09-02', '1068657448', 'Formacion', '3535704', 'De uso en el ambiente B105', 'Activo', NULL),
(52, 'Ingreso', 0, '2026-09-02', '1068657448', 'Formacion', NULL, '', 'Activo', NULL),
(53, 'Ingreso', 0, '2026-09-02', '1068657448', 'Formacion', NULL, '', 'Activo', NULL),
(54, 'Ingreso', 0, '2026-09-02', '1068657448', 'Formacion', NULL, '', 'Activo', NULL),
(55, 'Ingreso', 0, '2026-09-02', '1068657448', 'Formacion', NULL, '', 'Activo', NULL),
(56, 'Ingreso', 0, '2026-09-02', '1068657448', 'Formacion', NULL, '', 'Activo', NULL),
(57, 'Ingreso', 0, '2026-09-02', '1068657448', 'Formacion', NULL, '', 'Activo', NULL),
(58, 'Prestamo', 0, '2026-09-02', '1068657448', 'Formacion', '3578680', 'Adolfo Zarco  formación en emprender', 'Activo', NULL);

-- Reajustar secuencia de identidad
SELECT setval(pg_get_serial_sequence('inv_form_movimientos', 'id_movimiento'), 59, false);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla inv_form_usuarios
--

CREATE TABLE inv_form_usuarios (
  "Cedula" varchar(20) NOT NULL,
  "Nombres" varchar(150) NOT NULL,
  "Password" varchar(255) NOT NULL,
  "Tipo" text NOT NULL DEFAULT 'Usuario' CHECK ("Tipo" IN ('Admin','Usuario')),
  "Estado" text NOT NULL DEFAULT 'Activo' CHECK ("Estado" IN ('Activo','Inactivo'))
);

--
-- Volcado de datos para la tabla inv_form_usuarios
-- (contraseñas originales + nuevo usuario admin con clave 123456)
--

INSERT INTO inv_form_usuarios ("Cedula", "Nombres", "Password", "Tipo", "Estado") VALUES
('1068657448', 'José Fernando Sánchez Bruno', '$2y$10$TesjHBhzqZChNopQPW.1puEfwjiMUG1nnQsj6Y5U273DmSfWY/8d6', 'Admin', 'Activo'),
('10765884', 'Tonny López', '$2y$10$k/tnnlhK/l5bR/xOCvQSP.IPosMr6Pwi7UWgWT.BYkMXuSy/MbUEi', 'Admin', 'Activo'),
('1234567890', 'Usuario Administrador', '$2y$10$WOZCLaJaTry3wqUpZzRgb.S5tC/ThMDiKhQ4zcB/0r9C/b98qZ6Ti', 'Admin', 'Activo');

--
-- Índices para tablas volcadas
--

-- Claves primarias
ALTER TABLE inv_form_cuentadantes
  ADD PRIMARY KEY ("Cedula");

ALTER TABLE inv_form_det_movimientos
  ADD PRIMARY KEY ("Consecutivo");

ALTER TABLE inv_form_inventario
  ADD PRIMARY KEY ("Codigo");

ALTER TABLE inv_form_movimientos
  ADD PRIMARY KEY ("id_movimiento");

ALTER TABLE inv_form_usuarios
  ADD PRIMARY KEY ("Cedula");

-- Índices de llaves foráneas (equivalentes a ADD KEY de MySQL)
CREATE INDEX fk_det_movimiento ON inv_form_det_movimientos ("id_movimiento");
CREATE INDEX fk_det_inventario ON inv_form_det_movimientos ("Codigo_elemento");
CREATE INDEX fk_mov_cuentadante ON inv_form_movimientos ("Cedula_cuentadante");
CREATE INDEX fk_mov_ref ON inv_form_movimientos ("id_movimiento_ref");

--
-- Restricciones para tablas volcadas (FOREIGN KEYs)
--

ALTER TABLE inv_form_det_movimientos
  ADD CONSTRAINT fk_det_inventario FOREIGN KEY ("Codigo_elemento") REFERENCES inv_form_inventario ("Codigo") ON UPDATE CASCADE,
  ADD CONSTRAINT fk_det_movimiento FOREIGN KEY ("id_movimiento") REFERENCES inv_form_movimientos ("id_movimiento") ON DELETE CASCADE;

ALTER TABLE inv_form_movimientos
  ADD CONSTRAINT fk_mov_cuentadante FOREIGN KEY ("Cedula_cuentadante") REFERENCES inv_form_cuentadantes ("Cedula") ON UPDATE CASCADE,
  ADD CONSTRAINT fk_mov_ref FOREIGN KEY ("id_movimiento_ref") REFERENCES inv_form_movimientos ("id_movimiento") ON DELETE SET NULL;

COMMIT;

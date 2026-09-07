-- Base de datos Inventario_cm
-- Tablas con prefijo inv_form_ (debe coincidir con config/database.php → prefix)
CREATE DATABASE IF NOT EXISTS Inventario_cm
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE Inventario_cm;

-- Tabla Usuarios (incluye responsables de préstamos)
CREATE TABLE IF NOT EXISTS inv_form_Usuarios (
    Cedula VARCHAR(20) NOT NULL PRIMARY KEY,
    Nombres VARCHAR(150) NOT NULL,
    Password VARCHAR(255) NOT NULL,
    Tipo ENUM('Admin', 'Usuario') NOT NULL DEFAULT 'Usuario',
    Estado ENUM('Activo', 'Inactivo') NOT NULL DEFAULT 'Activo'
) ENGINE=InnoDB;

-- Tabla Categorias
CREATE TABLE IF NOT EXISTS inv_form_Categorias (
    id_categoria INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Nombre VARCHAR(100) NOT NULL,
    Estado ENUM('Activo', 'Inactivo', 'Eliminado') NOT NULL DEFAULT 'Activo'
) ENGINE=InnoDB;

-- Tabla Inventario
CREATE TABLE IF NOT EXISTS inv_form_Inventario (
    Codigo VARCHAR(50) NOT NULL PRIMARY KEY,
    Elemento VARCHAR(150) NOT NULL,
    id_categoria INT DEFAULT NULL,
    Descripcion TEXT,
    Cantidad INT NOT NULL DEFAULT 0,
    Fotografia VARCHAR(255) DEFAULT NULL,
    Estado ENUM('Activo', 'Inactivo', 'Eliminado') NOT NULL DEFAULT 'Activo',
    CONSTRAINT fk_inv_categoria FOREIGN KEY (id_categoria)
        REFERENCES inv_form_Categorias(id_categoria) ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Tabla Movimientos
CREATE TABLE IF NOT EXISTS inv_form_Movimientos (
    id_movimiento INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Tipo ENUM('Ingreso', 'Prestamo', 'Devolucion', 'Dar_Baja') NOT NULL,
    Consecutivo INT NOT NULL,
    Fecha DATE NOT NULL,
    Cedula_cuentadante VARCHAR(20) DEFAULT NULL,
    Descripcion TEXT,
    Estado ENUM('Activo', 'Inactivo', 'Cerrado') NOT NULL DEFAULT 'Activo',
    id_movimiento_ref INT DEFAULT NULL,
    CONSTRAINT fk_mov_cuentadante FOREIGN KEY (Cedula_cuentadante)
        REFERENCES inv_form_Usuarios(Cedula) ON UPDATE CASCADE,
    CONSTRAINT fk_mov_ref FOREIGN KEY (id_movimiento_ref)
        REFERENCES inv_form_Movimientos(id_movimiento) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Tabla Det_Movimientos
CREATE TABLE IF NOT EXISTS inv_form_Det_Movimientos (
    Consecutivo INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_movimiento INT NOT NULL,
    id_Detalle INT NOT NULL DEFAULT 1,
    Codigo_elemento VARCHAR(50) NOT NULL,
    Cantidad INT NOT NULL DEFAULT 1,
    Estado ENUM('Activo', 'Inactivo') NOT NULL DEFAULT 'Activo',
    CONSTRAINT fk_det_movimiento FOREIGN KEY (id_movimiento)
        REFERENCES inv_form_Movimientos(id_movimiento) ON DELETE CASCADE,
    CONSTRAINT fk_det_inventario FOREIGN KEY (Codigo_elemento)
        REFERENCES inv_form_Inventario(Codigo) ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Tabla Movimiento_Fotos
CREATE TABLE IF NOT EXISTS inv_form_Movimiento_Fotos (
    id_foto INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_movimiento INT NOT NULL,
    Ruta VARCHAR(255) NOT NULL,
    Orden TINYINT NOT NULL DEFAULT 1,
    CONSTRAINT fk_foto_movimiento FOREIGN KEY (id_movimiento)
        REFERENCES inv_form_Movimientos(id_movimiento) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabla Solicitudes_Prestamo
CREATE TABLE IF NOT EXISTS inv_form_Solicitudes_Prestamo (
    id_solicitud INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Cedula_solicitante VARCHAR(20) NOT NULL,
    Fecha_solicitud DATE NOT NULL,
    Descripcion TEXT,
    Estado ENUM('Pendiente', 'Aprobada', 'Rechazada', 'Cancelada') NOT NULL DEFAULT 'Pendiente',
    id_movimiento INT DEFAULT NULL,
    Cedula_aprobador VARCHAR(20) DEFAULT NULL,
    Fecha_resolucion DATETIME DEFAULT NULL,
    Motivo_rechazo TEXT DEFAULT NULL,
    CONSTRAINT fk_sol_solicitante FOREIGN KEY (Cedula_solicitante)
        REFERENCES inv_form_Usuarios(Cedula) ON UPDATE CASCADE,
    CONSTRAINT fk_sol_movimiento FOREIGN KEY (id_movimiento)
        REFERENCES inv_form_Movimientos(id_movimiento) ON DELETE SET NULL,
    CONSTRAINT fk_sol_aprobador FOREIGN KEY (Cedula_aprobador)
        REFERENCES inv_form_Usuarios(Cedula) ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS inv_form_Det_Solicitudes (
    id_detalle INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_solicitud INT NOT NULL,
    id_linea INT NOT NULL DEFAULT 1,
    Codigo_elemento VARCHAR(50) NOT NULL,
    Cantidad INT NOT NULL DEFAULT 1,
    CONSTRAINT fk_detsol_solicitud FOREIGN KEY (id_solicitud)
        REFERENCES inv_form_Solicitudes_Prestamo(id_solicitud) ON DELETE CASCADE,
    CONSTRAINT fk_detsol_inventario FOREIGN KEY (Codigo_elemento)
        REFERENCES inv_form_Inventario(Codigo) ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Datos iniciales
INSERT INTO inv_form_Categorias (Nombre, Estado) VALUES ('General', 'Activo');

INSERT INTO inv_form_Usuarios (Cedula, Nombres, Password, Tipo, Estado) VALUES
('admin', 'Administrador Sistema', '$2y$10$yfePygccbF.AC6m3bToDieNycmq2ZJXoSW9AXvKCwCBsnZ.P3tU.G', 'Admin', 'Activo'),
('9876543210', 'Juan Pérez García', '$2y$10$yfePygccbF.AC6m3bToDieNycmq2ZJXoSW9AXvKCwCBsnZ.P3tU.G', 'Usuario', 'Activo'),
('1122334455', 'María López Ruiz', '$2y$10$yfePygccbF.AC6m3bToDieNycmq2ZJXoSW9AXvKCwCBsnZ.P3tU.G', 'Usuario', 'Activo');
-- Password por defecto: 123456

INSERT INTO inv_form_Inventario (Codigo, Elemento, id_categoria, Descripcion, Cantidad, Estado) VALUES
('INV-001', 'Portátil Dell Latitude', 1, 'Equipo portátil para formación', 5, 'Activo'),
('INV-002', 'Cable HDMI 2m', 1, 'Cable HDMI estándar', 20, 'Activo'),
('INV-003', 'Proyector Epson', 1, 'Proyector sala B105', 2, 'Activo');

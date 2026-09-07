-- Migración: unificar cuentadantes en usuarios + tablas de solicitudes
-- Tablas con prefijo inv_form_ (debe coincidir con config/database.php → prefix)
USE Inventario_cm;

-- Copiar cuentadantes que no existan en usuarios
INSERT INTO inv_form_Usuarios (Cedula, Nombres, Password, Tipo, Estado)
SELECT c.Cedula, c.Nombres,
       '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy',
       'Usuario', c.Estado
FROM inv_form_Cuentadantes c
WHERE NOT EXISTS (SELECT 1 FROM inv_form_Usuarios u WHERE u.Cedula = c.Cedula);

-- Cédulas en movimientos sin usuario
INSERT INTO inv_form_Usuarios (Cedula, Nombres, Password, Tipo, Estado)
SELECT DISTINCT m.Cedula_cuentadante,
       CONCAT('Responsable ', m.Cedula_cuentadante),
       '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy',
       'Usuario', 'Activo'
FROM inv_form_Movimientos m
WHERE m.Cedula_cuentadante IS NOT NULL
  AND m.Cedula_cuentadante != ''
  AND NOT EXISTS (SELECT 1 FROM inv_form_Usuarios u WHERE u.Cedula = m.Cedula_cuentadante);

-- Reapuntar FK de movimientos a Usuarios
ALTER TABLE inv_form_Movimientos DROP FOREIGN KEY fk_mov_cuentadante;
ALTER TABLE inv_form_Movimientos
    ADD CONSTRAINT fk_mov_cuentadante FOREIGN KEY (Cedula_cuentadante)
        REFERENCES inv_form_Usuarios(Cedula) ON UPDATE CASCADE;

DROP TABLE IF EXISTS inv_form_Cuentadantes;

CREATE TABLE IF NOT EXISTS inv_form_Solicitudes_Prestamo (
    id_solicitud INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Cedula_solicitante VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    Fecha_solicitud DATE NOT NULL,
    Descripcion TEXT,
    Estado ENUM('Pendiente', 'Aprobada', 'Rechazada', 'Cancelada') NOT NULL DEFAULT 'Pendiente',
    id_movimiento INT DEFAULT NULL,
    Cedula_aprobador VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    Fecha_resolucion DATETIME DEFAULT NULL,
    Motivo_rechazo TEXT DEFAULT NULL,
    CONSTRAINT fk_sol_solicitante FOREIGN KEY (Cedula_solicitante)
        REFERENCES inv_form_Usuarios(Cedula) ON UPDATE CASCADE,
    CONSTRAINT fk_sol_movimiento FOREIGN KEY (id_movimiento)
        REFERENCES inv_form_Movimientos(id_movimiento) ON DELETE SET NULL,
    CONSTRAINT fk_sol_aprobador FOREIGN KEY (Cedula_aprobador)
        REFERENCES inv_form_Usuarios(Cedula) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inv_form_Det_Solicitudes (
    id_detalle INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_solicitud INT NOT NULL,
    id_linea INT NOT NULL DEFAULT 1,
    Codigo_elemento VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    Cantidad INT NOT NULL DEFAULT 1,
    CONSTRAINT fk_detsol_solicitud FOREIGN KEY (id_solicitud)
        REFERENCES inv_form_Solicitudes_Prestamo(id_solicitud) ON DELETE CASCADE,
    CONSTRAINT fk_detsol_inventario FOREIGN KEY (Codigo_elemento)
        REFERENCES inv_form_Inventario(Codigo) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categorías de inventario
-- Tablas con prefijo inv_form_ (debe coincidir con config/database.php → prefix)
CREATE TABLE IF NOT EXISTS inv_form_Categorias (
    id_categoria INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Nombre VARCHAR(100) NOT NULL,
    Estado ENUM('Activo', 'Inactivo', 'Eliminado') NOT NULL DEFAULT 'Activo'
) ENGINE=InnoDB;

INSERT INTO inv_form_Categorias (Nombre, Estado)
SELECT 'General', 'Activo'
WHERE NOT EXISTS (SELECT 1 FROM inv_form_Categorias LIMIT 1);

-- Categoría en inventario
ALTER TABLE inv_form_Inventario
    ADD COLUMN IF NOT EXISTS id_categoria INT DEFAULT NULL AFTER Elemento;

UPDATE inv_form_Inventario
SET id_categoria = (SELECT id_categoria FROM inv_form_Categorias WHERE Estado = 'Activo' ORDER BY id_categoria LIMIT 1)
WHERE id_categoria IS NULL;

-- Fotos de movimientos (dar de baja)
CREATE TABLE IF NOT EXISTS inv_form_Movimiento_Fotos (
    id_foto INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_movimiento INT NOT NULL,
    Ruta VARCHAR(255) NOT NULL,
    Orden TINYINT NOT NULL DEFAULT 1,
    CONSTRAINT fk_foto_movimiento FOREIGN KEY (id_movimiento)
        REFERENCES inv_form_Movimientos(id_movimiento) ON DELETE CASCADE
) ENGINE=InnoDB;

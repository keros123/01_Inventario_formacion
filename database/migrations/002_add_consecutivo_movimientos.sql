-- Add Consecutivo column to Movimientos for type-specific numbering
-- Tablas con prefijo inv_form_ (debe coincidir con config/database.php → prefix)
ALTER TABLE inv_form_Movimientos
ADD COLUMN Consecutivo INT NULL AFTER id_movimiento;

-- Backfill existing data with type-specific consecutive numbers
SET @ingreso = 0;
SET @prestamo = 0;
SET @devolucion = 0;

UPDATE inv_form_Movimientos
SET Consecutivo =
    CASE
        WHEN Tipo = 'Ingreso' THEN (@ingreso := @ingreso + 1)
        WHEN Tipo = 'Prestamo' THEN (@prestamo := @prestamo + 1)
        WHEN Tipo = 'Devolucion' THEN (@devolucion := @devolucion + 1)
    END
ORDER BY id_movimiento;

-- Make Consecutivo NOT NULL after backfilling
ALTER TABLE inv_form_Movimientos
MODIFY COLUMN Consecutivo INT NOT NULL;

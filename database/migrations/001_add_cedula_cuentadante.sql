-- Migración: agregar cuentadante a movimientos
-- Tablas con prefijo inv_form_ (debe coincidir con config/database.php → prefix)
USE InventarioB105;

ALTER TABLE inv_form_Movimientos
    ADD COLUMN Cedula_cuentadante VARCHAR(20) DEFAULT NULL AFTER Fecha,
    ADD CONSTRAINT fk_mov_cuentadante FOREIGN KEY (Cedula_cuentadante)
        REFERENCES inv_form_Cuentadantes(Cedula) ON UPDATE CASCADE;

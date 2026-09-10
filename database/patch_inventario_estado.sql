-- Alinea checks del dump original con lo que usa la app.

ALTER TABLE inv_form_movimientos DROP CONSTRAINT IF EXISTS "inv_form_movimientos_Tipo_check";
ALTER TABLE inv_form_movimientos DROP CONSTRAINT IF EXISTS inv_form_movimientos_tipo_check;
ALTER TABLE inv_form_movimientos
  ADD CONSTRAINT inv_form_movimientos_tipo_check
  CHECK ("Tipo" IN ('Ingreso', 'Prestamo', 'Devolucion', 'Dar_Baja'));

ALTER TABLE inv_form_inventario DROP CONSTRAINT IF EXISTS inv_form_inventario_Estado_check;
ALTER TABLE inv_form_inventario
  ADD CONSTRAINT inv_form_inventario_Estado_check
  CHECK ("Estado" IN ('Activo', 'Inactivo', 'Eliminado'));

ALTER TABLE inv_form_categorias DROP CONSTRAINT IF EXISTS inv_form_categorias_Estado_check;
ALTER TABLE inv_form_categorias
  ADD CONSTRAINT inv_form_categorias_Estado_check
  CHECK ("Estado" IN ('Activo', 'Inactivo', 'Eliminado'));

ALTER TABLE inv_form_movimientos
  ALTER COLUMN "id_cuentadante" SET DEFAULT 0;

ALTER TABLE inv_form_movimientos
  ALTER COLUMN "Uso" SET DEFAULT 'Formacion';

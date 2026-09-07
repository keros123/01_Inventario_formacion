-- Bucket público para fotos de inventario y dar de baja.
-- La app sube con la clave anon; sin estas políticas Storage responde 400/403.

INSERT INTO storage.buckets (id, name, public)
VALUES ('productos', 'productos', true)
ON CONFLICT (id) DO UPDATE SET public = true;

DROP POLICY IF EXISTS inv_form_storage_all ON storage.objects;
DROP POLICY IF EXISTS inv_form_storage_select ON storage.objects;
DROP POLICY IF EXISTS inv_form_storage_insert ON storage.objects;
DROP POLICY IF EXISTS inv_form_storage_update ON storage.objects;
DROP POLICY IF EXISTS inv_form_storage_delete ON storage.objects;

CREATE POLICY inv_form_storage_select ON storage.objects
    FOR SELECT TO anon, authenticated
    USING (bucket_id = 'productos');

CREATE POLICY inv_form_storage_insert ON storage.objects
    FOR INSERT TO anon, authenticated
    WITH CHECK (bucket_id = 'productos');

CREATE POLICY inv_form_storage_update ON storage.objects
    FOR UPDATE TO anon, authenticated
    USING (bucket_id = 'productos')
    WITH CHECK (bucket_id = 'productos');

CREATE POLICY inv_form_storage_delete ON storage.objects
    FOR DELETE TO anon, authenticated
    USING (bucket_id = 'productos');

ALTER TABLE inv_form_inventario
    ALTER COLUMN "Fotografia" TYPE varchar(500);

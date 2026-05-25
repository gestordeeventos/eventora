-- ============================================================
-- EVENTORA — Actualización: portadas de eventos + almacenamiento
-- Ejecutar en Supabase → SQL Editor
-- ============================================================

-- 1. Columnas en tabla eventos (imagen_url ya existe en esquema base)
ALTER TABLE eventos
    ADD COLUMN IF NOT EXISTS imagen_updated_at TIMESTAMP NULL;

COMMENT ON COLUMN eventos.imagen_url IS
    'Ruta relativa de la portada cuadrada (ej. eventos/portadas/uuid.jpg) o URL pública si usa Supabase Storage';

COMMENT ON COLUMN eventos.imagen_updated_at IS
    'Última vez que se actualizó la imagen de portada del evento';

-- Índice opcional para consultas de eventos con portada
CREATE INDEX IF NOT EXISTS eventos_imagen_url_idx
    ON eventos (imagen_url)
    WHERE imagen_url IS NOT NULL;

-- ============================================================
-- 2. Supabase Storage — bucket público para portadas
-- (También puedes crearlo en Dashboard → Storage → New bucket)
-- ============================================================

INSERT INTO storage.buckets (id, name, public, file_size_limit, allowed_mime_types)
VALUES (
    'eventos-portadas',
    'eventos-portadas',
    true,
    3145728,
    ARRAY['image/jpeg', 'image/jpg', 'image/png']::text[]
)
ON CONFLICT (id) DO UPDATE SET
    public = EXCLUDED.public,
    file_size_limit = EXCLUDED.file_size_limit,
    allowed_mime_types = EXCLUDED.allowed_mime_types;

-- Lectura pública de portadas
DROP POLICY IF EXISTS "Portadas públicas lectura" ON storage.objects;
CREATE POLICY "Portadas públicas lectura"
    ON storage.objects FOR SELECT
    USING (bucket_id = 'eventos-portadas');

-- Solo usuarios autenticados (admin vía API) pueden subir
DROP POLICY IF EXISTS "Portadas subida autenticada" ON storage.objects;
CREATE POLICY "Portadas subida autenticada"
    ON storage.objects FOR INSERT
    WITH CHECK (
        bucket_id = 'eventos-portadas'
        AND auth.role() = 'authenticated'
    );

DROP POLICY IF EXISTS "Portadas actualización autenticada" ON storage.objects;
CREATE POLICY "Portadas actualización autenticada"
    ON storage.objects FOR UPDATE
    USING (bucket_id = 'eventos-portadas' AND auth.role() = 'authenticated');

DROP POLICY IF EXISTS "Portadas eliminación autenticada" ON storage.objects;
CREATE POLICY "Portadas eliminación autenticada"
    ON storage.objects FOR DELETE
    USING (bucket_id = 'eventos-portadas' AND auth.role() = 'authenticated');

-- ============================================================
-- 3. Notas de integración Laravel
-- ============================================================
-- Desarrollo local (recomendado para empezar):
--   php artisan storage:link
--   Las portadas se guardan en storage/app/public/eventos/portadas/
--   y se sirven en /storage/eventos/portadas/...
--
-- Producción con Supabase Storage (opcional):
--   Configura en .env el driver S3 compatible de Supabase y
--   guarda en imagen_url la URL pública del objeto subido.
-- ============================================================

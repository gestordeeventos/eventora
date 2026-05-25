-- ============================================================
-- EVENTORA — Fotos de perfil de clientes
-- Ejecutar en Supabase → SQL Editor
-- ============================================================

ALTER TABLE usuarios
    ADD COLUMN IF NOT EXISTS foto_perfil_url TEXT NULL,
    ADD COLUMN IF NOT EXISTS foto_perfil_updated_at TIMESTAMP NULL;

COMMENT ON COLUMN usuarios.foto_perfil_url IS
    'Ruta relativa de avatar cuadrado (ej. usuarios/avatars/uuid.jpg) o URL pública en Supabase Storage';

COMMENT ON COLUMN usuarios.foto_perfil_updated_at IS
    'Última actualización de la foto de perfil del usuario';

CREATE INDEX IF NOT EXISTS usuarios_foto_perfil_idx
    ON usuarios (foto_perfil_url)
    WHERE foto_perfil_url IS NOT NULL;

-- Bucket público para avatares de clientes
INSERT INTO storage.buckets (id, name, public, file_size_limit, allowed_mime_types)
VALUES (
    'usuarios-avatars',
    'usuarios-avatars',
    true,
    2097152,
    ARRAY['image/jpeg', 'image/jpg', 'image/png']::text[]
)
ON CONFLICT (id) DO UPDATE SET
    public = EXCLUDED.public,
    file_size_limit = EXCLUDED.file_size_limit,
    allowed_mime_types = EXCLUDED.allowed_mime_types;

DROP POLICY IF EXISTS "Avatares lectura pública" ON storage.objects;
CREATE POLICY "Avatares lectura pública"
    ON storage.objects FOR SELECT
    USING (bucket_id = 'usuarios-avatars');

DROP POLICY IF EXISTS "Avatares subida autenticada" ON storage.objects;
CREATE POLICY "Avatares subida autenticada"
    ON storage.objects FOR INSERT
    WITH CHECK (bucket_id = 'usuarios-avatars' AND auth.role() = 'authenticated');

DROP POLICY IF EXISTS "Avatares actualización autenticada" ON storage.objects;
CREATE POLICY "Avatares actualización autenticada"
    ON storage.objects FOR UPDATE
    USING (bucket_id = 'usuarios-avatars' AND auth.role() = 'authenticated');

DROP POLICY IF EXISTS "Avatares eliminación autenticada" ON storage.objects;
CREATE POLICY "Avatares eliminación autenticada"
    ON storage.objects FOR DELETE
    USING (bucket_id = 'usuarios-avatars' AND auth.role() = 'authenticated');

-- Laravel local: php artisan storage:link
-- Archivos en storage/app/public/usuarios/avatars/

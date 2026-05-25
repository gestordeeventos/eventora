-- ============================================================
-- EVENTORA — Pago simulado, tickets, OAuth y usuarios
-- Ejecutar en Supabase → SQL Editor
-- ============================================================

-- Reservas: ticket y pago
ALTER TABLE reservas
    ADD COLUMN IF NOT EXISTS codigo_ticket VARCHAR(24) UNIQUE,
    ADD COLUMN IF NOT EXISTS pagado_at TIMESTAMP NULL,
    ADD COLUMN IF NOT EXISTS metodo_pago VARCHAR(40),
    ADD COLUMN IF NOT EXISTS ultimos4_tarjeta CHAR(4);

CREATE INDEX IF NOT EXISTS reservas_codigo_ticket_idx ON reservas (codigo_ticket);

-- Usuarios: OAuth (password opcional si entra por red social)
ALTER TABLE usuarios
    ADD COLUMN IF NOT EXISTS google_id VARCHAR(255) UNIQUE,
    ADD COLUMN IF NOT EXISTS facebook_id VARCHAR(255) UNIQUE;

ALTER TABLE usuarios ALTER COLUMN password_hash DROP NOT NULL;

COMMENT ON COLUMN usuarios.google_id IS 'ID de cuenta Google (Socialite)';
COMMENT ON COLUMN usuarios.facebook_id IS 'ID de cuenta Facebook (Socialite)';

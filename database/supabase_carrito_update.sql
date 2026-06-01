-- ============================================================
-- EVENTORA — Carrito de compras
-- Ejecutar en Supabase → SQL Editor
-- ============================================================

CREATE TABLE IF NOT EXISTS carritos (
    id_carrito   BIGSERIAL PRIMARY KEY,
    id_usuario   BIGINT NOT NULL REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    estado       VARCHAR(20) NOT NULL DEFAULT 'activo'
                 CHECK (estado IN ('activo', 'checkout', 'convertido')),
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS carritos_usuario_estado_idx ON carritos (id_usuario, estado);

CREATE TABLE IF NOT EXISTS carrito_items (
    id_item          BIGSERIAL PRIMARY KEY,
    id_carrito       BIGINT NOT NULL REFERENCES carritos(id_carrito) ON DELETE CASCADE,
    id_evento        BIGINT NOT NULL REFERENCES eventos(id_evento) ON DELETE RESTRICT,
    id_boleto        BIGINT NOT NULL REFERENCES boletos(id_boleto) ON DELETE RESTRICT,
    cantidad         INT NOT NULL CHECK (cantidad > 0),
    precio_unitario  DECIMAL(10, 2) NOT NULL,
    created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (id_carrito, id_boleto)
);

CREATE INDEX IF NOT EXISTS carrito_items_carrito_idx ON carrito_items (id_carrito);

ALTER TABLE reservas
    ADD COLUMN IF NOT EXISTS id_carrito BIGINT REFERENCES carritos(id_carrito) ON DELETE SET NULL;

CREATE INDEX IF NOT EXISTS reservas_carrito_idx ON reservas (id_carrito);

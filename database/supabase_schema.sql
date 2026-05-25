-- ============================================================
-- EVENTORA — Esquema limpio (solo tablas necesarias)
-- Supabase → SQL Editor → Run
-- ============================================================

-- 1. Eliminar tablas que NO se usan
DROP TABLE IF EXISTS reserva_boletos CASCADE;
DROP TABLE IF EXISTS reservas CASCADE;
DROP TABLE IF EXISTS boletos CASCADE;
DROP TABLE IF EXISTS evento_paquete CASCADE;
DROP TABLE IF EXISTS eventos CASCADE;
DROP TABLE IF EXISTS paquetes CASCADE;
DROP TABLE IF EXISTS tipos_evento CASCADE;
DROP TABLE IF EXISTS usuarios CASCADE;
DROP TABLE IF EXISTS failed_jobs CASCADE;
DROP TABLE IF EXISTS job_batches CASCADE;
DROP TABLE IF EXISTS jobs CASCADE;
DROP TABLE IF EXISTS cache_locks CASCADE;
DROP TABLE IF EXISTS cache CASCADE;
DROP TABLE IF EXISTS users CASCADE;
DROP TABLE IF EXISTS sessions CASCADE;
DROP TABLE IF EXISTS password_reset_tokens CASCADE;
DROP TABLE IF EXISTS migrations CASCADE;

-- ============================================================
-- 2. Tabla de sesiones (login Laravel)
-- ============================================================

CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload TEXT NOT NULL,
    last_activity INT NOT NULL
);
CREATE INDEX sessions_user_id_index ON sessions (user_id);
CREATE INDEX sessions_last_activity_index ON sessions (last_activity);

-- ============================================================
-- 3. Tablas de negocio (interconectadas)
-- ============================================================

CREATE TABLE tipos_evento (
    id_tipo_evento   SERIAL PRIMARY KEY,
    nombre           VARCHAR(80) NOT NULL UNIQUE,
    descripcion      TEXT,
    activo           BOOLEAN NOT NULL DEFAULT TRUE,
    created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE usuarios (
    id_usuario       BIGSERIAL PRIMARY KEY,
    nombre           VARCHAR(80) NOT NULL,
    apellido         VARCHAR(80) NOT NULL,
    email            VARCHAR(120) NOT NULL UNIQUE,
    password_hash    VARCHAR(255) NOT NULL,
    rol              VARCHAR(20) NOT NULL DEFAULT 'cliente'
                     CHECK (rol IN ('admin', 'organizador', 'cliente')),
    telefono         VARCHAR(20),
    foto_perfil_url  TEXT,
    foto_perfil_updated_at TIMESTAMP NULL,
    activo           BOOLEAN NOT NULL DEFAULT TRUE,
    email_verificado_at TIMESTAMP NULL,
    remember_token   VARCHAR(100),
    created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE eventos (
    id_evento        BIGSERIAL PRIMARY KEY,
    id_organizador   BIGINT NOT NULL REFERENCES usuarios(id_usuario) ON DELETE RESTRICT,
    id_tipo_evento   INT NOT NULL REFERENCES tipos_evento(id_tipo_evento) ON DELETE RESTRICT,
    titulo           VARCHAR(150) NOT NULL,
    descripcion      TEXT,
    fecha_inicio     TIMESTAMP NOT NULL,
    fecha_fin        TIMESTAMP,
    lugar            VARCHAR(200) NOT NULL,
    ciudad           VARCHAR(100),
    capacidad_max    INT NOT NULL CHECK (capacidad_max > 0),
    estado           VARCHAR(20) NOT NULL DEFAULT 'borrador'
                     CHECK (estado IN ('borrador', 'publicado', 'cancelado', 'finalizado')),
    imagen_url       TEXT,
    imagen_updated_at TIMESTAMP NULL,
    created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX eventos_organizador_idx ON eventos(id_organizador);
CREATE INDEX eventos_tipo_idx ON eventos(id_tipo_evento);
CREATE INDEX eventos_estado_idx ON eventos(estado);
CREATE INDEX eventos_fecha_idx ON eventos(fecha_inicio);

CREATE TABLE paquetes (
    id_paquete       SERIAL PRIMARY KEY,
    nombre           VARCHAR(100) NOT NULL,
    descripcion      TEXT,
    precio           DECIMAL(10, 2) NOT NULL CHECK (precio >= 0),
    incluye          TEXT,
    activo           BOOLEAN NOT NULL DEFAULT TRUE,
    created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE evento_paquete (
    id_evento        BIGINT NOT NULL REFERENCES eventos(id_evento) ON DELETE CASCADE,
    id_paquete       INT NOT NULL REFERENCES paquetes(id_paquete) ON DELETE RESTRICT,
    PRIMARY KEY (id_evento, id_paquete)
);

CREATE TABLE boletos (
    id_boleto        BIGSERIAL PRIMARY KEY,
    id_evento        BIGINT NOT NULL REFERENCES eventos(id_evento) ON DELETE CASCADE,
    nombre_tipo      VARCHAR(50) NOT NULL,
    precio           DECIMAL(10, 2) NOT NULL CHECK (precio >= 0),
    cantidad_total   INT NOT NULL CHECK (cantidad_total > 0),
    cantidad_vendida INT NOT NULL DEFAULT 0 CHECK (cantidad_vendida >= 0),
    created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (id_evento, nombre_tipo)
);
CREATE INDEX boletos_evento_idx ON boletos(id_evento);

CREATE TABLE reservas (
    id_reserva       BIGSERIAL PRIMARY KEY,
    id_usuario       BIGINT NOT NULL REFERENCES usuarios(id_usuario) ON DELETE RESTRICT,
    id_evento        BIGINT NOT NULL REFERENCES eventos(id_evento) ON DELETE RESTRICT,
    id_paquete       INT REFERENCES paquetes(id_paquete) ON DELETE SET NULL,
    estado           VARCHAR(20) NOT NULL DEFAULT 'pendiente'
                     CHECK (estado IN ('pendiente', 'confirmada', 'cancelada', 'pagada')),
    total            DECIMAL(10, 2) NOT NULL DEFAULT 0 CHECK (total >= 0),
    notas            TEXT,
    created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX reservas_usuario_idx ON reservas(id_usuario);
CREATE INDEX reservas_evento_idx ON reservas(id_evento);

CREATE TABLE reserva_boletos (
    id               SERIAL PRIMARY KEY,
    id_reserva       BIGINT NOT NULL REFERENCES reservas(id_reserva) ON DELETE CASCADE,
    id_boleto        BIGINT NOT NULL REFERENCES boletos(id_boleto) ON DELETE RESTRICT,
    cantidad         INT NOT NULL CHECK (cantidad > 0),
    precio_unitario  DECIMAL(10, 2) NOT NULL,
    subtotal         DECIMAL(10, 2) NOT NULL,
    UNIQUE (id_reserva, id_boleto)
);

-- Sesiones ligadas a usuarios
ALTER TABLE sessions
    ADD CONSTRAINT sessions_user_id_foreign
    FOREIGN KEY (user_id) REFERENCES usuarios(id_usuario) ON DELETE CASCADE;

-- ============================================================
-- 4. Datos iniciales
-- ============================================================

INSERT INTO tipos_evento (nombre, descripcion) VALUES
    ('Corporativo', 'Eventos empresariales, conferencias y lanzamientos'),
    ('Social', 'Fiestas, reuniones y celebraciones privadas'),
    ('Conferencia', 'Congresos, seminarios y charlas profesionales'),
    ('Boda', 'Ceremonias y recepciones nupciales')
ON CONFLICT (nombre) DO NOTHING;

INSERT INTO paquetes (nombre, descripcion, precio, incluye)
SELECT v.nombre, v.descripcion, v.precio, v.incluye
FROM (VALUES
    ('Básico', 'Coordinación esencial del evento', 1500.00, 'Coordinador, cronograma, checklist'),
    ('Profesional', 'Logística completa y proveedores', 4500.00, 'Coordinador, logística, catering básico, decoración'),
    ('Premium', 'Experiencia integral llave en mano', 9000.00, 'Todo Profesional + DJ, fotografía, transporte VIP')
) AS v(nombre, descripcion, precio, incluye)
WHERE NOT EXISTS (SELECT 1 FROM paquetes p WHERE p.nombre = v.nombre);

INSERT INTO usuarios (nombre, apellido, email, password_hash, rol, activo)
SELECT 'Admin', 'Eventora', 'admin@gestoreventos.com',
       '$2y$12$3o2LuWODR0yunATr0WBnuOMe7ofvLO3lnP394WJQ6bSA5N1GGyia.', 'admin', TRUE
WHERE NOT EXISTS (SELECT 1 FROM usuarios u WHERE u.email = 'admin@gestoreventos.com');

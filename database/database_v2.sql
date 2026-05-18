-- =========================================================
-- PET SPA - DATABASE V2
-- PostgreSQL + Laravel Compatible
-- =========================================================

CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- =========================================================
-- ROLES Y PERMISOS
-- =========================================================

CREATE TABLE roles (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT,
    created_at TIMESTAMP DEFAULT now(),
    updated_at TIMESTAMP DEFAULT now()
);

CREATE TABLE permisos (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    created_at TIMESTAMP DEFAULT now(),
    updated_at TIMESTAMP DEFAULT now()
);

CREATE TABLE usuario_roles (
    usuario_id UUID NOT NULL,
    rol_id BIGINT NOT NULL,
    PRIMARY KEY (usuario_id, rol_id)
);

CREATE TABLE rol_permisos (
    rol_id BIGINT NOT NULL,
    permiso_id BIGINT NOT NULL,
    PRIMARY KEY (rol_id, permiso_id)
);

-- =========================================================
-- USUARIOS
-- =========================================================

CREATE TABLE usuarios (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),

    nombre_completo VARCHAR(150) NOT NULL,

    email VARCHAR(150) NOT NULL UNIQUE,

    password VARCHAR(255) NOT NULL,

    telefono VARCHAR(20),

    ci VARCHAR(30),

    google_id VARCHAR(255),

    avatar_url TEXT,

    email_verified_at TIMESTAMP NULL,

    verification_token VARCHAR(255),

    verification_expires_at TIMESTAMP NULL,

    password_reset_token VARCHAR(255),

    password_reset_expires_at TIMESTAMP NULL,

    two_factor_secret TEXT,

    two_factor_enabled BOOLEAN DEFAULT false,

    backup_codes JSONB,

    failed_login_attempts INTEGER DEFAULT 0,

    locked_until TIMESTAMP NULL,

    jwt_version INTEGER DEFAULT 0,

    is_active BOOLEAN DEFAULT true,

    last_login TIMESTAMP NULL,

    remember_token VARCHAR(100),

    created_at TIMESTAMP DEFAULT now(),

    updated_at TIMESTAMP DEFAULT now(),

    deleted_at TIMESTAMP NULL
);

-- =========================================================
-- CLIENTES
-- =========================================================

CREATE TABLE clientes (
    usuario_id UUID PRIMARY KEY,

    direccion TEXT,

    preferencia_comunicacion VARCHAR(20) DEFAULT 'WHATSAPP',

    newsletter BOOLEAN DEFAULT true,

    created_at TIMESTAMP DEFAULT now(),

    updated_at TIMESTAMP DEFAULT now(),

    deleted_at TIMESTAMP NULL
);

-- =========================================================
-- EMPLEADOS
-- =========================================================

CREATE TABLE empleados (
    usuario_id UUID PRIMARY KEY,

    tipo_empleado VARCHAR(30) NOT NULL,

    especialidad VARCHAR(100),

    turno VARCHAR(50),

    fecha_contratacion DATE,

    max_servicios_simultaneos INTEGER DEFAULT 1,

    created_at TIMESTAMP DEFAULT now(),

    updated_at TIMESTAMP DEFAULT now(),

    deleted_at TIMESTAMP NULL
);

CREATE TABLE groomers (
    groomer_id UUID PRIMARY KEY,

    especialidades TEXT[],

    created_at TIMESTAMP DEFAULT now(),

    updated_at TIMESTAMP DEFAULT now()
);

-- =========================================================
-- PROVEEDORES
-- =========================================================

CREATE TABLE proveedores (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),

    nombre VARCHAR(150) NOT NULL,

    contacto VARCHAR(100),

    telefono VARCHAR(20),

    email VARCHAR(150),

    direccion TEXT,

    is_active BOOLEAN DEFAULT true,

    created_at TIMESTAMP DEFAULT now(),

    updated_at TIMESTAMP DEFAULT now(),

    deleted_at TIMESTAMP NULL
);

-- =========================================================
-- SERVICIOS
-- =========================================================

CREATE TABLE servicios (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),

    nombre VARCHAR(100) NOT NULL,

    descripcion TEXT,

    duracion_base_minutos INTEGER NOT NULL,

    precio_base DECIMAL(10,2) NOT NULL,

    incremento_pequenio DECIMAL(5,2) DEFAULT 0,

    incremento_mediano DECIMAL(5,2) DEFAULT 15,

    incremento_grande DECIMAL(5,2) DEFAULT 30,

    categoria VARCHAR(50),

    activo BOOLEAN DEFAULT true,

    created_at TIMESTAMP DEFAULT now(),

    updated_at TIMESTAMP DEFAULT now(),

    deleted_at TIMESTAMP NULL
);

-- =========================================================
-- PRODUCTOS
-- =========================================================

CREATE TABLE productos (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),

    nombre VARCHAR(150) NOT NULL,

    unidad_medida VARCHAR(20) NOT NULL,

    costo DECIMAL(10,2) NOT NULL,

    precio_venta DECIMAL(10,2) NOT NULL,

    stock_actual DECIMAL(10,3) DEFAULT 0,

    stock_minimo DECIMAL(10,3) DEFAULT 0,

    categoria VARCHAR(100),

    proveedor_id UUID,

    activo BOOLEAN DEFAULT true,

    created_at TIMESTAMP DEFAULT now(),

    updated_at TIMESTAMP DEFAULT now(),

    deleted_at TIMESTAMP NULL
);

-- =========================================================
-- HORARIOS Y AGENDA
-- =========================================================

CREATE TABLE horario_trabajo_groomer (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),

    groomer_id UUID NOT NULL,

    dia_semana INTEGER NOT NULL CHECK (dia_semana BETWEEN 0 AND 6),

    hora_inicio TIME NOT NULL,

    hora_fin TIME NOT NULL,

    pausa_inicio TIME,

    pausa_fin TIME,

    created_at TIMESTAMP DEFAULT now(),

    updated_at TIMESTAMP DEFAULT now()
);

CREATE TABLE bloqueos_agenda (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),

    groomer_id UUID NOT NULL,

    fecha DATE NOT NULL,

    todo_el_dia BOOLEAN DEFAULT true,

    hora_inicio TIME,

    hora_fin TIME,

    motivo VARCHAR(255),

    created_by UUID NOT NULL,

    created_at TIMESTAMP DEFAULT now(),

    updated_at TIMESTAMP DEFAULT now()
);

-- =========================================================
-- MASCOTAS
-- =========================================================

CREATE TABLE mascotas (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),

    cliente_id UUID NOT NULL,

    nombre VARCHAR(100) NOT NULL,

    especie VARCHAR(50) NOT NULL,

    raza VARCHAR(100),

    tamanio VARCHAR(20) NOT NULL,

    edad INTEGER,

    unidad_edad VARCHAR(10) DEFAULT 'MESES',

    color VARCHAR(50),

    peso_kg DECIMAL(5,2),

    caracteristicas_fisicas TEXT,

    restricciones_medicas TEXT,

    fotos TEXT[],

    created_at TIMESTAMP DEFAULT now(),

    updated_at TIMESTAMP DEFAULT now(),

    deleted_at TIMESTAMP NULL
);

CREATE TABLE vacunas (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),

    mascota_id UUID NOT NULL,

    nombre_vacuna VARCHAR(100) NOT NULL,

    fecha_aplicacion DATE,

    fecha_expiracion DATE,

    documento_url VARCHAR(255),

    created_at TIMESTAMP DEFAULT now(),

    updated_at TIMESTAMP DEFAULT now()
);

-- =========================================================
-- CITAS
-- =========================================================

CREATE TABLE citas (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),

    cliente_id UUID NOT NULL,

    mascota_id UUID NOT NULL,

    servicio_id UUID NOT NULL,

    groomer_id UUID NOT NULL,

    fecha DATE NOT NULL,

    hora_inicio TIME NOT NULL,

    hora_fin TIME NOT NULL,

    estado VARCHAR(20) DEFAULT 'PROGRAMADO',

    motivo_cancelacion TEXT,

    calificacion_promedio DECIMAL(2,1),

    creada_por UUID NOT NULL,

    created_at TIMESTAMP DEFAULT now(),

    updated_at TIMESTAMP DEFAULT now(),

    deleted_at TIMESTAMP NULL
);

-- =========================================================
-- FEEDBACK
-- =========================================================

CREATE TABLE feedback_servicio (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),

    cita_id UUID NOT NULL UNIQUE,

    puntuacion INTEGER NOT NULL CHECK (puntuacion BETWEEN 1 AND 5),

    comentario TEXT,

    created_at TIMESTAMP DEFAULT now(),

    updated_at TIMESTAMP DEFAULT now()
);

-- =========================================================
-- FICHAS GROOMING
-- =========================================================

CREATE TABLE fichas_grooming (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),

    cita_id UUID NOT NULL UNIQUE,

    groomer_id UUID NOT NULL,

    estado_ingreso_nudos VARCHAR(20),

    estado_ingreso_pulgas BOOLEAN,

    estado_ingreso_heridas TEXT,

    temperamento VARCHAR(20),

    condicion_general TEXT,

    recomendaciones_tecnicas TEXT,

    tiempo_real_minutos INTEGER,

    fecha_apertura TIMESTAMP DEFAULT now(),

    fecha_cierre TIMESTAMP,

    created_at TIMESTAMP DEFAULT now(),

    updated_at TIMESTAMP DEFAULT now()
);

-- =========================================================
-- INVENTARIO
-- =========================================================

CREATE TABLE movimientos_inventario (
    id BIGSERIAL PRIMARY KEY,

    producto_id UUID NOT NULL,

    tipo_movimiento VARCHAR(20) NOT NULL,

    cantidad DECIMAL(10,3) NOT NULL,

    motivo VARCHAR(50),

    referencia_id VARCHAR(36),

    usuario_id UUID NOT NULL,

    fecha TIMESTAMP DEFAULT now()
);

-- =========================================================
-- AUDITORIA
-- =========================================================

CREATE TABLE auditoria_log (
    id BIGSERIAL PRIMARY KEY,

    usuario_id UUID,

    accion VARCHAR(100) NOT NULL,

    tabla_afectada VARCHAR(100),

    registro_id VARCHAR(36),

    datos_antes JSONB,

    datos_despues JSONB,

    ip_address INET,

    user_agent TEXT,

    created_at TIMESTAMP DEFAULT now()
);

-- =========================================================
-- SESIONES
-- =========================================================

CREATE TABLE sesiones_usuario (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),

    usuario_id UUID NOT NULL,

    ip_address INET,

    user_agent TEXT,

    last_activity TIMESTAMP DEFAULT now(),

    created_at TIMESTAMP DEFAULT now()
);

-- =========================================================
-- FOREIGN KEYS
-- =========================================================

ALTER TABLE usuario_roles
ADD CONSTRAINT fk_usuario_roles_usuario
FOREIGN KEY (usuario_id)
REFERENCES usuarios(id)
ON DELETE CASCADE;

ALTER TABLE usuario_roles
ADD CONSTRAINT fk_usuario_roles_rol
FOREIGN KEY (rol_id)
REFERENCES roles(id);

ALTER TABLE rol_permisos
ADD CONSTRAINT fk_rol_permisos_rol
FOREIGN KEY (rol_id)
REFERENCES roles(id);

ALTER TABLE rol_permisos
ADD CONSTRAINT fk_rol_permisos_permiso
FOREIGN KEY (permiso_id)
REFERENCES permisos(id);

ALTER TABLE clientes
ADD CONSTRAINT fk_clientes_usuario
FOREIGN KEY (usuario_id)
REFERENCES usuarios(id)
ON DELETE CASCADE;

ALTER TABLE empleados
ADD CONSTRAINT fk_empleados_usuario
FOREIGN KEY (usuario_id)
REFERENCES usuarios(id)
ON DELETE CASCADE;

ALTER TABLE groomers
ADD CONSTRAINT fk_groomers_empleado
FOREIGN KEY (groomer_id)
REFERENCES empleados(usuario_id)
ON DELETE CASCADE;

ALTER TABLE productos
ADD CONSTRAINT fk_productos_proveedor
FOREIGN KEY (proveedor_id)
REFERENCES proveedores(id);

ALTER TABLE horario_trabajo_groomer
ADD CONSTRAINT fk_horario_groomer
FOREIGN KEY (groomer_id)
REFERENCES groomers(groomer_id);

ALTER TABLE bloqueos_agenda
ADD CONSTRAINT fk_bloqueo_groomer
FOREIGN KEY (groomer_id)
REFERENCES groomers(groomer_id);

ALTER TABLE bloqueos_agenda
ADD CONSTRAINT fk_bloqueo_usuario
FOREIGN KEY (created_by)
REFERENCES usuarios(id);

ALTER TABLE mascotas
ADD CONSTRAINT fk_mascotas_cliente
FOREIGN KEY (cliente_id)
REFERENCES clientes(usuario_id);

ALTER TABLE vacunas
ADD CONSTRAINT fk_vacunas_mascota
FOREIGN KEY (mascota_id)
REFERENCES mascotas(id)
ON DELETE CASCADE;

ALTER TABLE citas
ADD CONSTRAINT fk_citas_cliente
FOREIGN KEY (cliente_id)
REFERENCES clientes(usuario_id);

ALTER TABLE citas
ADD CONSTRAINT fk_citas_mascota
FOREIGN KEY (mascota_id)
REFERENCES mascotas(id);

ALTER TABLE citas
ADD CONSTRAINT fk_citas_servicio
FOREIGN KEY (servicio_id)
REFERENCES servicios(id);

ALTER TABLE citas
ADD CONSTRAINT fk_citas_groomer
FOREIGN KEY (groomer_id)
REFERENCES groomers(groomer_id);

ALTER TABLE citas
ADD CONSTRAINT fk_citas_usuario
FOREIGN KEY (creada_por)
REFERENCES usuarios(id);

ALTER TABLE feedback_servicio
ADD CONSTRAINT fk_feedback_cita
FOREIGN KEY (cita_id)
REFERENCES citas(id)
ON DELETE CASCADE;

ALTER TABLE fichas_grooming
ADD CONSTRAINT fk_ficha_cita
FOREIGN KEY (cita_id)
REFERENCES citas(id);

ALTER TABLE fichas_grooming
ADD CONSTRAINT fk_ficha_groomer
FOREIGN KEY (groomer_id)
REFERENCES groomers(groomer_id);

ALTER TABLE movimientos_inventario
ADD CONSTRAINT fk_movimientos_producto
FOREIGN KEY (producto_id)
REFERENCES productos(id);

ALTER TABLE movimientos_inventario
ADD CONSTRAINT fk_movimientos_usuario
FOREIGN KEY (usuario_id)
REFERENCES usuarios(id);

ALTER TABLE auditoria_log
ADD CONSTRAINT fk_auditoria_usuario
FOREIGN KEY (usuario_id)
REFERENCES usuarios(id);

ALTER TABLE sesiones_usuario
ADD CONSTRAINT fk_sesion_usuario
FOREIGN KEY (usuario_id)
REFERENCES usuarios(id)
ON DELETE CASCADE;

-- =========================================================
-- INDICES
-- =========================================================

CREATE INDEX idx_usuarios_email ON usuarios(email);
CREATE INDEX idx_usuarios_active ON usuarios(is_active);
CREATE INDEX idx_usuarios_google_id ON usuarios(google_id);

CREATE INDEX idx_citas_fecha_hora
ON citas(fecha, hora_inicio, hora_fin);

CREATE INDEX idx_citas_groomer_estado
ON citas(groomer_id, estado);

CREATE INDEX idx_auditoria_usuario
ON auditoria_log(usuario_id);

CREATE INDEX idx_auditoria_fecha
ON auditoria_log(created_at);

CREATE INDEX idx_productos_stock
ON productos(stock_actual);

CREATE INDEX idx_mascotas_cliente
ON mascotas(cliente_id);

-- =========================================================
-- DATOS INICIALES
-- =========================================================

INSERT INTO roles(nombre, descripcion)
VALUES
('ADMIN', 'Administrador del sistema'),
('RECEPCION', 'Personal de recepción'),
('GROOMER', 'Personal grooming'),
('CLIENTE', 'Cliente del sistema');

-- =========================================================
-- NOTAS
-- =========================================================
-- Compatible con Laravel + Sanctum
-- Compatible con OAuth Google
-- Compatible con 2FA
-- Compatible con Soft Deletes
-- Compatible con PostgreSQL

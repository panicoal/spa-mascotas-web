--Habilitar extensión UUID
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";


-- Roles
CREATE TABLE roles (
    id INTEGER PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
);

-- Permisos
CREATE TABLE permisos (
    id INTEGER PRIMARY KEY,
    nombre VARCHAR(100) UNIQUE,
    descripcion TEXT
);

-- Usuarios
CREATE TABLE usuarios (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    two_factor_secret VARCHAR(255),
    two_factor_enabled BOOLEAN DEFAULT false,
    backup_codes TEXT[],
    jwt_version INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT true,
    last_login TIMESTAMP,
    created_at TIMESTAMP DEFAULT now(),
    updated_at TIMESTAMP DEFAULT now()
);

-- Proveedores
CREATE TABLE proveedores (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    nombre VARCHAR(150) NOT NULL,
    contacto VARCHAR(100),
    telefono VARCHAR(20),
    email VARCHAR(150),
    direccion TEXT
);

-- Servicios
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
    created_at TIMESTAMP DEFAULT now()
);

-- Productos
CREATE TABLE productos (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    nombre VARCHAR(150) NOT NULL,
    unidad_medida VARCHAR(20) NOT NULL,
    costo DECIMAL(10,2) NOT NULL,
    precio_venta DECIMAL(10,2) NOT NULL,
    stock_actual DECIMAL(10,3) DEFAULT 0,
    stock_minimo DECIMAL(10,3) DEFAULT 0,
    categoria VARCHAR(100),
    proveedor_id UUID REFERENCES proveedores(id),
    activo BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT now()
);


-- Usuario_roles (relación N:M)
CREATE TABLE usuario_roles (
    usuario_id UUID REFERENCES usuarios(id) ON DELETE CASCADE,
    rol_id INTEGER REFERENCES roles(id),
    PRIMARY KEY (usuario_id, rol_id)
);

-- Rol_permisos
CREATE TABLE rol_permisos (
    rol_id INTEGER REFERENCES roles(id),
    permiso_id INTEGER REFERENCES permisos(id),
    PRIMARY KEY (rol_id, permiso_id)
);

-- Auditoría
CREATE TABLE auditoria_log (
    id BIGSERIAL PRIMARY KEY,
    usuario_id UUID REFERENCES usuarios(id),
    accion VARCHAR(100) NOT NULL,
    tabla_afectada VARCHAR(100),
    registro_id VARCHAR(36),
    datos_antes JSONB,
    datos_despues JSONB,
    ip_address INET,
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT now()
);

-- Clientes
CREATE TABLE clientes (
    usuario_id UUID PRIMARY KEY REFERENCES usuarios(id) ON DELETE CASCADE,
    telefono VARCHAR(20) NOT NULL,
    direccion TEXT,
    preferencia_comunicacion VARCHAR(20) DEFAULT 'WHATSAPP',
    newsletter BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT now(),
    updated_at TIMESTAMP DEFAULT now()
);

-- Empleados
CREATE TABLE empleados (
    usuario_id UUID PRIMARY KEY REFERENCES usuarios(id),
    tipo_empleado VARCHAR(20) NOT NULL,
    fecha_contratacion DATE,
    max_servicios_simultaneos INTEGER DEFAULT 1
);

-- Groomers
CREATE TABLE groomers (
    groomer_id UUID PRIMARY KEY REFERENCES empleados(usuario_id),
    especialidades TEXT[]
);

-- Horario trabajo groomer
CREATE TABLE horario_trabajo_groomer (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    groomer_id UUID NOT NULL REFERENCES groomers(groomer_id),
    dia_semana INTEGER NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    pausa_inicio TIME,
    pausa_fin TIME
);

-- Bloques agenda
CREATE TABLE bloqueos_agenda (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    groomer_id UUID NOT NULL REFERENCES groomers(groomer_id),
    fecha DATE NOT NULL,
    todo_el_dia BOOLEAN DEFAULT true,
    hora_inicio TIME,
    hora_fin TIME,
    motivo VARCHAR(255),
    created_by UUID NOT NULL REFERENCES usuarios(id)
);

-- Mascotas y citas

-- Mascotas
CREATE TABLE mascotas (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    cliente_id UUID NOT NULL REFERENCES clientes(usuario_id),
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
    updated_at TIMESTAMP DEFAULT now()
);

-- Vacunas
CREATE TABLE vacunas (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    mascota_id UUID NOT NULL REFERENCES mascotas(id),
    nombre_vacuna VARCHAR(100) NOT NULL,
    fecha_aplicacion DATE,
    fecha_expiracion DATE,
    documento_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT now()
);

-- Citas
CREATE TABLE citas (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    cliente_id UUID NOT NULL REFERENCES clientes(usuario_id),
    mascota_id UUID NOT NULL REFERENCES mascotas(id),
    servicio_id UUID NOT NULL REFERENCES servicios(id),
    groomer_id UUID NOT NULL REFERENCES groomers(groomer_id),
    fecha DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    estado VARCHAR(20) DEFAULT 'PROGRAMADO',
    motivo_cancelacion TEXT,
    calificacion_promedio DECIMAL(2,1),
    creada_por UUID NOT NULL REFERENCES usuarios(id),
    created_at TIMESTAMP DEFAULT now(),
    updated_at TIMESTAMP DEFAULT now()
);

-- Feedback servicio
CREATE TABLE feedback_servicio (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    cita_id UUID NOT NULL UNIQUE REFERENCES citas(id) ON DELETE CASCADE,
    puntuacion INTEGER NOT NULL CHECK (puntuacion BETWEEN 1 AND 5),
    comentario TEXT,
    created_at TIMESTAMP DEFAULT now()
);

-- Fichas Grooming y cherklist

-- Fichas grooming
CREATE TABLE fichas_grooming (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    cita_id UUID NOT NULL UNIQUE REFERENCES citas(id),
    groomer_id UUID NOT NULL REFERENCES groomers(groomer_id),
    estado_ingreso_nudos VARCHAR(20),
    estado_ingreso_pulgas BOOLEAN,
    estado_ingreso_heridas TEXT,
    temperamento VARCHAR(20),
    condicion_general TEXT,
    recomendaciones_tecnicas TEXT,
    tiempo_real_minutos INTEGER,
    fecha_apertura TIMESTAMP DEFAULT now(),
    fecha_cierre TIMESTAMP
);

-- Checklist plantilla
CREATE TABLE checklist_plantilla (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    servicio_id UUID NOT NULL REFERENCES servicios(id),
    nombre_item VARCHAR(100) NOT NULL,
    orden INTEGER
);

-- Checklist ficha
CREATE TABLE checklist_ficha (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    ficha_id UUID NOT NULL REFERENCES fichas_grooming(id),
    item_id UUID NOT NULL REFERENCES checklist_plantilla(id),
    completado BOOLEAN DEFAULT false,
    observacion TEXT
);

-- Fotos ficha
CREATE TABLE fotos_ficha (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    ficha_id UUID NOT NULL REFERENCES fichas_grooming(id),
    tipo VARCHAR(10),
    url VARCHAR(255) NOT NULL,
    orden INTEGER
);

-- Consumo insumos por ficha
CREATE TABLE consumo_insumos_ficha (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    ficha_id UUID NOT NULL REFERENCES fichas_grooming(id),
    producto_id UUID NOT NULL REFERENCES productos(id),
    cantidad DECIMAL(10,3) NOT NULL
);

--Inventario y movimientos

-- Variantes de producto
CREATE TABLE variantes_producto (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    producto_id UUID NOT NULL REFERENCES productos(id),
    atributo VARCHAR(50),
    valor VARCHAR(100),
    precio_extra DECIMAL(10,2) DEFAULT 0
);

-- Movimientos inventario 
CREATE TABLE movimientos_inventario (
    id BIGSERIAL PRIMARY KEY,
    producto_id UUID NOT NULL REFERENCES productos(id),
    tipo_movimiento VARCHAR(20) NOT NULL,
    cantidad DECIMAL(10,3) NOT NULL,
    motivo VARCHAR(50),
    referencia_id VARCHAR(36),
    usuario_id UUID NOT NULL REFERENCES usuarios(id),
    fecha TIMESTAMP DEFAULT now()
);

--Ventas, carrito, pedidos

-- Carrito compras
CREATE TABLE carrito_compras (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    cliente_id UUID NOT NULL REFERENCES clientes(usuario_id),
    created_at TIMESTAMP DEFAULT now()
);

-- Carrito items
CREATE TABLE carrito_items (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    carrito_id UUID NOT NULL REFERENCES carrito_compras(id),
    producto_id UUID NOT NULL REFERENCES productos(id),
    cantidad DECIMAL(10,3) NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL
);

-- Pedidos
CREATE TABLE pedidos (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    cliente_id UUID NOT NULL REFERENCES clientes(usuario_id),
    tipo_pedido VARCHAR(20) NOT NULL,
    cita_id UUID REFERENCES citas(id),
    estado VARCHAR(20) DEFAULT 'PENDIENTE',
    subtotal DECIMAL(10,2) NOT NULL,
    descuento DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT now()
);

-- Pedido items
CREATE TABLE pedido_items (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    pedido_id UUID NOT NULL REFERENCES pedidos(id),
    producto_id UUID REFERENCES productos(id),
    servicio_id UUID REFERENCES servicios(id),
    cantidad DECIMAL(10,3),
    precio_unitario DECIMAL(10,2) NOT NULL
);

-- Pagos, comprobantes, caja
-- Pagos
CREATE TABLE pagos (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    pedido_id UUID NOT NULL REFERENCES pedidos(id),
    monto DECIMAL(10,2) NOT NULL,
    metodo_pago VARCHAR(20) NOT NULL,
    referencia_pago VARCHAR(100),
    estado VARCHAR(20) DEFAULT 'COMPLETADO',
    fecha TIMESTAMP DEFAULT now()
);

-- Comprobantes
CREATE TABLE comprobantes (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    pedido_id UUID NOT NULL REFERENCES pedidos(id),
    tipo VARCHAR(20) NOT NULL,
    url_pdf VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT now()
);

-- Sesiones caja
CREATE TABLE sesiones_caja (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    usuario_id UUID NOT NULL REFERENCES usuarios(id),
    fecha_apertura TIMESTAMP NOT NULL,
    monto_apertura DECIMAL(10,2) DEFAULT 0,
    fecha_cierre TIMESTAMP,
    monto_cierre DECIMAL(10,2),
    total_ingresos DECIMAL(10,2)
);

--Compras a proveedores
-- Ordenes compra
CREATE TABLE ordenes_compra (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    proveedor_id UUID NOT NULL REFERENCES proveedores(id),
    fecha DATE NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    estado VARCHAR(20),
    created_by UUID NOT NULL REFERENCES usuarios(id)
);

-- Compra items
CREATE TABLE compra_items (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    orden_compra_id UUID NOT NULL REFERENCES ordenes_compra(id),
    producto_id UUID NOT NULL REFERENCES productos(id),
    cantidad DECIMAL(10,3) NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL
);

--Notificaciones y promociones
-- Plantillas notificaciones
CREATE TABLE plantillas_notificaciones (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    codigo VARCHAR(50) NOT NULL UNIQUE,
    asunto VARCHAR(255) NOT NULL,
    cuerpo TEXT NOT NULL,
    activa BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT now()
);

-- Notificaciones
CREATE TABLE notificaciones (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    usuario_id UUID NOT NULL REFERENCES usuarios(id),
    plantilla_id UUID REFERENCES plantillas_notificaciones(id),
    tipo_canal VARCHAR(20) NOT NULL,
    asunto VARCHAR(255),
    contenido TEXT,
    referencia_tipo VARCHAR(50),
    referencia_id VARCHAR(36),
    estado_envio VARCHAR(20) DEFAULT 'PENDIENTE',
    fecha_programada TIMESTAMP,
    fecha_envio TIMESTAMP,
    created_at TIMESTAMP DEFAULT now()
);

-- Promociones
CREATE TABLE promociones (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    nombre VARCHAR(100) NOT NULL,
    tipo VARCHAR(20) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    aplica_a VARCHAR(20),
    fecha_inicio DATE,
    fecha_fin DATE,
    codigo_promocional VARCHAR(50),
    activa BOOLEAN DEFAULT true
);

-- Promociones aplicadas
CREATE TABLE promociones_aplicadas (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    pedido_id UUID NOT NULL REFERENCES pedidos(id),
    promocion_id UUID NOT NULL REFERENCES promociones(id),
    monto_descuento DECIMAL(10,2) NOT NULL
);

--Índices importantes 

-- Índices para citas (requerimiento CLI01, AG03)
CREATE INDEX IX_citas_fecha_hora ON citas(fecha, hora_inicio, hora_fin);
CREATE INDEX IX_citas_groomer_estado ON citas(groomer_id, estado);
CREATE INDEX IX_citas_cliente ON citas(cliente_id);

-- Auditoría
CREATE INDEX IX_auditoria_usuario ON auditoria_log(usuario_id);
CREATE INDEX IX_auditoria_table_fecha ON auditoria_log(tabla_afectada, created_at);
CREATE INDEX IX_auditoria_accion ON auditoria_log(accion);

-- Mascotas
CREATE INDEX IX_mascotas_cliente ON mascotas(cliente_id);
CREATE INDEX IX_mascotas_especie_raza ON mascotas(especie, raza);

-- Productos
CREATE INDEX IX_productos_stock ON productos(stock_actual) WHERE stock_actual <= stock_minimo;
CREATE INDEX IX_movimientos_producto ON movimientos_inventario(producto_id, fecha);

--Trigger para descontar inventario automáticamente (FT05)
-- Función que actualiza stock y registra movimiento
CREATE OR REPLACE FUNCTION actualizar_stock_por_consumo()
RETURNS TRIGGER AS $$
BEGIN
    -- Descontar stock del producto
    UPDATE productos
    SET stock_actual = stock_actual - NEW.cantidad
    WHERE id = NEW.producto_id;

    -- Insertar movimiento de inventario (quien ejecuta? se puede pasar usuario desde la aplicación, pero como trigger no tiene contexto, usaremos un usuario sistema o null. En la práctica, el backend también podría hacerlo. Dejamos un usuario dummy)
    INSERT INTO movimientos_inventario (producto_id, tipo_movimiento, cantidad, motivo, referencia_id, usuario_id)
    VALUES (NEW.producto_id, 'SALIDA', NEW.cantidad, 'CONSUMO_SERVICIO', NEW.ficha_id::text, (SELECT id FROM usuarios LIMIT 1));

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger que se activa por cada inserción en consumo_insumos_ficha
CREATE TRIGGER trigger_descontar_stock
AFTER INSERT ON consumo_insumos_ficha
FOR EACH ROW
EXECUTE FUNCTION actualizar_stock_por_consumo();

/*
Nota: Este trigger usa un usuario fijo (SELECT id FROM usuarios LIMIT 1). 
En producción deberías pasar el usuario real desde el backend. 
Como opción alternativa, puedes manejar la actualización de stock desde NestJS 
(centralizando lógica) y no usar trigger. La elección es tuya.
RESUMEN PASAR LA LOGICA A BACKEND
*/

-- usuario ejemplo sin hash aun
INSERT INTO usuarios (email, password_hash) 
VALUES ('admin@spa.com', '1234');
CREATE DATABASE IF NOT EXISTS ecommerce;
USE ecommerce;

-- ============================================================
-- TABLA: usuarios_roles (tabla principal de usuarios)
-- ============================================================
CREATE TABLE usuarios_roles (
  id INT(11) NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  telefono VARCHAR(20) DEFAULT NULL,
  rol ENUM('cliente','admin','inventario','contador','vendedor') DEFAULT 'cliente',
  activo TINYINT(1) DEFAULT 1,
  fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_email (email),
  KEY idx_rol (rol),
  KEY idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: categorias
-- ============================================================
CREATE TABLE categorias (
  id INT(11) NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL,
  descripcion TEXT DEFAULT NULL,
  imagen VARCHAR(255) DEFAULT NULL,
  activo TINYINT(1) DEFAULT 1,
  fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO categorias (nombre, descripcion, imagen, activo) VALUES
('Camisetas', 'Camisetas deportivas para hombre y mujer', NULL, 1),
('Zapatos', 'Calzado deportivo de alto rendimiento', NULL, 1),
('Pantalonetas', 'Pantalonetas y shorts deportivos', NULL, 1),
('Accesorios', 'Medias, gorras, bolsos y más', NULL, 1);

-- ============================================================
-- TABLA: productos
-- ============================================================
CREATE TABLE productos (
  id INT(11) NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(150) NOT NULL,
  descripcion TEXT DEFAULT NULL,
  categoria_id INT(11) DEFAULT NULL,
  imagen_principal VARCHAR(255) DEFAULT NULL,
  coleccion ENUM('nueva_temporada','outlet','mas_vendidos','normal') DEFAULT 'normal',
  activo TINYINT(1) DEFAULT 1,
  fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY categoria_id (categoria_id),
  CONSTRAINT productos_ibfk_1 FOREIGN KEY (categoria_id) REFERENCES categorias (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO productos (nombre, descripcion, categoria_id, imagen_principal, coleccion, activo) VALUES
('Camiseta Colombia visitante 2026', 'Camiseta de fútbol de la Selección Colombia en colores de visitante, con tecnología Climacool para un juego fresco y seco.', 1, 'prod_69d08daaf0a6b.png', 'nueva_temporada', 1),
('Camiseta local alemania 2026', 'Camiseta de fútbol de ajuste ceñido con tecnología Climacool que mantiene el cuerpo fresco y seco.', 1, 'prod_69d1468675f8e.png', 'nueva_temporada', 1),
('Nike Court Vision Low', 'El estilo Fastbreak del básquetbol de los 80 y la cultura de ritmo rápido del deporte actual se combinan en los Nike Court Vision Low.', 2, NULL, 'nueva_temporada', 0),
('Nike Court Vision Low', 'El estilo Fastbreak del básquetbol de los 80 y la cultura de ritmo rápido del deporte actual se combinan en los Nike Court Vision Low.', 2, 'prod_69d14ba234d8b.png', 'nueva_temporada', 1),
('SHORTS D4T WORKOUT 2 EN 1', 'Corte clásico con tecnología CLIMACOOL, tejido elástico de cuatro vías, material absorbente y de secado rápido.', 3, 'prod_69e6c01d61bdd.png', 'nueva_temporada', 1);

-- ============================================================
-- TABLA: variantes
-- ============================================================
CREATE TABLE variantes (
  id INT(11) NOT NULL AUTO_INCREMENT,
  producto_id INT(11) NOT NULL,
  talla VARCHAR(10) NOT NULL,
  color VARCHAR(50) NOT NULL,
  precio DECIMAL(10,2) NOT NULL,
  stock INT(11) DEFAULT 0,
  activo TINYINT(1) DEFAULT 1,
  PRIMARY KEY (id),
  KEY idx_variantes_producto (producto_id),
  CONSTRAINT variantes_ibfk_1 FOREIGN KEY (producto_id) REFERENCES productos (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO variantes (producto_id, talla, color, precio, stock, activo) VALUES
(1, 'XL', 'Azul', 359.00, 20, 1),
(2, 'L', 'Blanco', 80.00, 50, 1),
(3, '40', 'Negro', 110.00, 10, 1),
(4, '40', 'Blanco', 110.00, 20, 1),
(5, 'L', 'Negro', 62.00, 30, 1);

-- ============================================================
-- TABLA: cupones
-- ============================================================
CREATE TABLE cupones (
  id INT(11) NOT NULL AUTO_INCREMENT,
  codigo VARCHAR(50) NOT NULL,
  tipo ENUM('porcentaje','monto_fijo') NOT NULL,
  descuento DECIMAL(10,2) NOT NULL,
  usos_maximos INT(11) DEFAULT 1,
  usos_actuales INT(11) DEFAULT 0,
  fecha_vencimiento DATE DEFAULT NULL,
  activo TINYINT(1) DEFAULT 1,
  fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY codigo (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO cupones (codigo, tipo, descuento, usos_maximos, activo) VALUES
('BIENVENIDA', 'porcentaje', 10.00, 999, 1),
('DESCUENTO20', 'porcentaje', 20.00, 999, 1),
('MONTO100', 'monto_fijo', 100.00, 50, 1);

-- ============================================================
-- TABLA: ordenes (CORREGIDA: apunta a usuarios_roles)
-- ============================================================
CREATE TABLE ordenes (
  id INT(11) NOT NULL AUTO_INCREMENT,
  usuario_id INT(11) NOT NULL,
  cupon_id INT(11) DEFAULT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  descuento DECIMAL(10,2) DEFAULT 0.00,
  total DECIMAL(10,2) NOT NULL,
  estado ENUM('pendiente','empacado','enviado','entregado','cancelado') DEFAULT 'pendiente',
  direccion_envio TEXT DEFAULT NULL,
  fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY cupon_id (cupon_id),
  KEY idx_ordenes_usuario (usuario_id),
  KEY idx_ordenes_estado (estado),
  CONSTRAINT ordenes_ibfk_1 FOREIGN KEY (usuario_id) REFERENCES usuarios_roles (id) ON DELETE CASCADE,
  CONSTRAINT ordenes_ibfk_2 FOREIGN KEY (cupon_id) REFERENCES cupones (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- TABLA: detalle_orden
-- ============================================================
CREATE TABLE detalle_orden (
  id INT(11) NOT NULL AUTO_INCREMENT,
  orden_id INT(11) NOT NULL,
  variante_id INT(11) NOT NULL,
  cantidad INT(11) NOT NULL,
  precio_unitario DECIMAL(10,2) NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY orden_id (orden_id),
  KEY variante_id (variante_id),
  CONSTRAINT detalle_orden_ibfk_1 FOREIGN KEY (orden_id) REFERENCES ordenes (id) ON DELETE CASCADE,
  CONSTRAINT detalle_orden_ibfk_2 FOREIGN KEY (variante_id) REFERENCES variantes (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- TABLA: facturas (NUEVA: completamente funcional)
-- ============================================================
CREATE TABLE facturas (
  id INT(11) NOT NULL AUTO_INCREMENT,
  orden_id INT(11) NOT NULL UNIQUE,
  numero_factura VARCHAR(50) NOT NULL UNIQUE,
  fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  CONSTRAINT facturas_ibfk_1 FOREIGN KEY (orden_id) REFERENCES ordenes (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- TABLA: movimientos_inventario (CORREGIDA: apunta a usuarios_roles)
-- ============================================================
CREATE TABLE movimientos_inventario (
  id INT(11) NOT NULL AUTO_INCREMENT,
  variante_id INT(11) NOT NULL,
  tipo ENUM('entrada','salida') NOT NULL,
  cantidad INT(11) NOT NULL,
  motivo VARCHAR(150) DEFAULT NULL,
  usuario_id INT(11) DEFAULT NULL,
  fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY usuario_id (usuario_id),
  KEY idx_movimientos_variante (variante_id),
  CONSTRAINT movimientos_inventario_ibfk_1 FOREIGN KEY (variante_id) REFERENCES variantes (id) ON DELETE CASCADE,
  CONSTRAINT movimientos_inventario_ibfk_2 FOREIGN KEY (usuario_id) REFERENCES usuarios_roles (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO movimientos_inventario (variante_id, tipo, cantidad, motivo, usuario_id) VALUES
(1, 'entrada', 20, 'Stock inicial de variante', NULL),
(2, 'entrada', 50, 'Stock inicial de variante', NULL),
(3, 'entrada', 10, 'Stock inicial de variante', NULL),
(4, 'entrada', 20, 'Stock inicial de variante', NULL),
(5, 'entrada', 30, 'Stock inicial de variante', NULL);

-- ============================================================
-- TABLA: direcciones (CORREGIDA: apunta a usuarios_roles)
-- ============================================================
CREATE TABLE direcciones (
  id INT(11) NOT NULL AUTO_INCREMENT,
  usuario_id INT(11) NOT NULL,
  direccion VARCHAR(255) NOT NULL,
  ciudad VARCHAR(100) NOT NULL,
  departamento VARCHAR(100) NOT NULL,
  codigo_postal VARCHAR(20) DEFAULT NULL,
  principal TINYINT(1) DEFAULT 0,
  PRIMARY KEY (id),
  KEY usuario_id (usuario_id),
  CONSTRAINT direcciones_ibfk_1 FOREIGN KEY (usuario_id) REFERENCES usuarios_roles (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- TABLA: pagos (CORREGIDA: apunta a ordenes correctamente)
-- ============================================================
CREATE TABLE pagos (
  id INT(11) NOT NULL AUTO_INCREMENT,
  orden_id INT(11) NOT NULL,
  metodo ENUM('efectivo','transferencia','tarjeta') NOT NULL,
  estado ENUM('pendiente','completado','rechazado') DEFAULT 'pendiente',
  referencia VARCHAR(100) DEFAULT NULL,
  fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY orden_id (orden_id),
  CONSTRAINT pagos_ibfk_1 FOREIGN KEY (orden_id) REFERENCES ordenes (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- TABLA: wishlist (CORREGIDA: apunta a usuarios_roles)
-- ============================================================
CREATE TABLE wishlist (
  id INT(11) NOT NULL AUTO_INCREMENT,
  usuario_id INT(11) NOT NULL,
  variante_id INT(11) NOT NULL,
  fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY unique_wishlist (usuario_id, variante_id),
  KEY usuario_id (usuario_id),
  KEY variante_id (variante_id),
  CONSTRAINT wishlist_ibfk_1 FOREIGN KEY (usuario_id) REFERENCES usuarios_roles (id) ON DELETE CASCADE,
  CONSTRAINT wishlist_ibfk_2 FOREIGN KEY (variante_id) REFERENCES variantes (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- TABLA: roles (tabla de referencia)
-- ============================================================
CREATE TABLE roles (
  id INT(11) NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(50) NOT NULL UNIQUE,
  descripcion TEXT DEFAULT NULL,
  permisos LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  activo TINYINT(1) DEFAULT 1,
  fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY nombre (nombre),
  KEY idx_nombre (nombre),
  KEY idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO roles (nombre, descripcion, permisos, activo) VALUES
('super_admin', 'Super administrador - Acceso total', '{\"usuarios\": true, \"roles\": true, \"productos\": true, \"inventario\": true, \"pedidos\": true, \"cupones\": true, \"reportes\": true, \"configuracion\": true}', 1),
('admin', 'Administrador - Gestión de catálogo y pedidos', '{\"usuarios\": false, \"roles\": false, \"productos\": true, \"inventario\": true, \"pedidos\": true, \"cupones\": true, \"reportes\": true, \"configuracion\": false}', 1),
('contador', 'Contador - Acceso a reportes y pedidos', '{\"usuarios\": false, \"roles\": false, \"productos\": false, \"inventario\": false, \"pedidos\": true, \"cupones\": false, \"reportes\": true, \"configuracion\": false}', 1),
('inventario', 'Gestor de Inventario - Gestión de stock', '{\"usuarios\": false, \"roles\": false, \"productos\": false, \"inventario\": true, \"pedidos\": false, \"cupones\": false, \"reportes\": false, \"configuracion\": false}', 1),
('vendedor', 'Vendedor - Gestión de ventas', '{\"usuarios\": false, \"roles\": false, \"productos\": false, \"inventario\": false, \"pedidos\": true, \"cupones\": true, \"reportes\": false, \"configuracion\": false}', 1),
('cliente', 'Cliente - Acceso cliente', '{\"usuarios\": false, \"roles\": false, \"productos\": false, \"inventario\": false, \"pedidos\": false, \"cupones\": false, \"reportes\": false, \"configuracion\": false}', 1);

COMMIT;


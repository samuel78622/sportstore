USE ecommerce;

-- ============================================================
-- INSERTAR USUARIOS CON ROLES
-- ============================================================
-- Contraseñas: admin123, cliente123, contador123, vendedor123, inventario123

INSERT INTO usuarios_roles (nombre, email, password, telefono, rol, activo) VALUES
('Administrador', 'admin@ecommerce.com', '$2y$10$RgHtK5z6Hei5a.45g2kh.ODK7angDQJDUqP06KSePzPVdq9he2jcO', '3001234567', 'admin', 1),
('Juan Pérez', 'juan@example.com', '$2y$10$kf7LLTXgOYJwfthgqRGGEesZqFs0.h6F0lkj5PpMU/XIk5UQJoDBC', '3007654321', 'cliente', 1),
('María Contador', 'maria@ecommerce.com', '$2y$10$9OLzEc3u6kv37XefWm8uVegeHjq3oa68eVBESsMo3i2HGtb1p0SIO', '3009876543', 'contador', 1),
('Carlos Vendedor', 'carlos@ecommerce.com', '$2y$10$PzSh2tI.ZO6JGGanSNnSC.wY3t1e4GxRwQop3HKCDn1oh1TgQYe.K', '3005555555', 'vendedor', 1),
('Luis Inventario', 'luis@ecommerce.com', '$2y$10$OqPe6ID7idne258G5L7U3.YsQZnLOHWYRB1.omcEvBPnnVvZGq9L.', '3004444444', 'inventario', 1),
('Andrea García', 'andreagutierrez@hotmail.com', '$2y$10$mGnz8JOTwAVlDGpNTYv9suFZebYsAg/0kKyZvj5p6/tCneA/d7QvK', '302355789', 'cliente', 1),
('Samuel Chaparro', 'samuelchaparro88@gmail.com', '$2y$10$v4GJ2bWDBLQgY7Info8VwesroEwelpMXZSRM675KqXNclxudKb7zS', '3152231327', 'cliente', 1);

-- ============================================================
-- CREAR ÓRDENES DE PRUEBA
-- ============================================================
INSERT INTO ordenes (usuario_id, cupon_id, subtotal, descuento, total, estado, direccion_envio) VALUES
(2, NULL, 359.00, 0.00, 359.00, 'pendiente', 'Carrera 5 # 123-456, Bogotá'),
(2, NULL, 80.00, 0.00, 80.00, 'pendiente', 'Carrera 5 # 123-456, Bogotá'),
(2, NULL, 110.00, 0.00, 110.00, 'pendiente', 'Carrera 5 # 123-456, Bogotá'),
(6, NULL, 190.00, 0.00, 190.00, 'pendiente', 'Calle 10 # 45-78, Medellín'),
(7, NULL, 62.00, 0.00, 62.00, 'pendiente', 'Avenida 19 # 100-50, Cali');

-- ============================================================
-- CREAR DETALLES DE ÓRDENES
-- ============================================================
INSERT INTO detalle_orden (orden_id, variante_id, cantidad, precio_unitario, subtotal) VALUES
(1, 1, 1, 359.00, 359.00),
(2, 2, 1, 80.00, 80.00),
(3, 3, 1, 110.00, 110.00),
(4, 4, 1, 110.00, 110.00),
(4, 5, 1, 80.00, 80.00),
(5, 5, 1, 62.00, 62.00);

-- ============================================================
-- CREAR FACTURAS PARA LAS ÓRDENES
-- ============================================================
INSERT INTO facturas (orden_id, numero_factura) VALUES
(1, 'FAC-20260511-00001'),
(2, 'FAC-20260511-00002'),
(3, 'FAC-20260511-00003'),
(4, 'FAC-20260511-00004'),
(5, 'FAC-20260511-00005');

-- ============================================================
-- CREAR DIRECCIONES DE USUARIOS
-- ============================================================
INSERT INTO direcciones (usuario_id, direccion, ciudad, departamento, codigo_postal, principal) VALUES
(2, 'Carrera 5 # 123-456', 'Bogotá', 'Cundinamarca', '110111', 1),
(6, 'Calle 10 # 45-78', 'Medellín', 'Antioquia', '050001', 1),
(7, 'Avenida 19 # 100-50', 'Cali', 'Valle del Cauca', '760001', 1);

COMMIT;
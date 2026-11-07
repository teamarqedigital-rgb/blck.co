-- blck.co · datos de prueba
-- Usuarios: admin@blck.co / Admin123*   ·   barista@blck.co / Barista123*
-- Selecciona la base de datos deseada antes de importar.
-- Importa este archivo una sola vez por base: contiene registros de demostración.

INSERT INTO rol (nombre) VALUES ('administrador'), ('barista');

INSERT INTO usuario (nombre, correo, clave, rol_id) VALUES
('Admin blck.co', 'admin@blck.co', '$2y$10$Iyjz9UZq/E/Dtow/F8.sqOU98jVw8y6D/4Dyzuvx/.TCEZ7XkZXTC', 1),
('Valentina Ríos', 'barista@blck.co', '$2y$10$0EAUbeZVVPG3SkzfXKNPY.fFhrNvEt6D2kIY8mZWBrschJwmZDIce', 2);

INSERT INTO cliente (nombre, telefono, correo) VALUES
('Laura Gómez', '3001234567', 'laura@correo.com'),
('Carlos Pérez', '3109998888', 'carlos@correo.com'),
('Mariana Torres', '3205551122', 'mariana@correo.com'),
('Andrés Ruiz', '3017778899', NULL),
('Sofía Herrera', '3153334455', 'sofia@correo.com'),
('Mostrador', NULL, NULL);

INSERT INTO categoria (nombre) VALUES
('Café caliente'), ('Café frío'), ('Panadería'), ('Postres'), ('Bebidas');

INSERT INTO producto (nombre, descripcion, precio, stock, categoria_id) VALUES
('Espresso', 'Doble shot, cuerpo intenso', 5500, 60, 1),
('Americano', 'Espresso largo con agua caliente', 6000, 60, 1),
('Cappuccino', 'Espresso, leche vaporizada y espuma', 8500, 50, 1),
('Latte', 'Espresso con leche cremosa', 9000, 50, 1),
('Cold Brew', 'Extracción en frío durante 18 horas', 10500, 30, 2),
('Frappé de moca', 'Café, chocolate y hielo', 12500, 25, 2),
('Croissant', 'Mantequilla, horneado en el día', 7000, 20, 3),
('Pan de bono', 'Queso y almidón de yuca', 3500, 40, 3),
('Brownie', 'Chocolate oscuro 70 %', 8000, 15, 4),
('Cheesecake', 'Base de galleta, frutos rojos', 11000, 8, 4),
('Agua con gas', '350 ml', 4500, 35, 5),
('Chocolate caliente', 'Cacao 60 %, leche entera', 8500, 30, 5);

INSERT INTO pedido (cliente_id, usuario_id, fecha, estado, total) VALUES
(1, 2, NOW() - INTERVAL 40 DAY, 'entregado', 24000),
(2, 2, NOW() - INTERVAL 30 DAY, 'entregado', 20500),
(1, 2, NOW() - INTERVAL 20 DAY, 'entregado', 33500),
(3, 2, NOW() - INTERVAL 10 DAY, 'entregado', 17500),
(1, 2, NOW() - INTERVAL 3 DAY,  'entregado', 22500),
(2, 2, NOW() - INTERVAL 2 DAY,  'entregado', 15000),
(1, 2, NOW() - INTERVAL 1 DAY,  'entregado', 26000),
(4, 2, NOW(), 'pendiente', 14500);

INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio_unitario) VALUES
(1, 3, 2, 8500), (1, 7, 1, 7000),
(2, 4, 1, 9000), (2, 9, 1, 8000), (2, 8, 1, 3500) ,
(3, 5, 2, 10500), (3, 6, 1, 12500),
(4, 4, 1, 9000), (4, 3, 1, 8500),
(5, 3, 1, 8500), (5, 5, 1, 10500), (5, 8, 1, 3500) ,
(6, 1, 1, 5500), (6, 2, 1, 6000), (6, 8, 1, 3500),
(7, 4, 2, 9000), (7, 9, 1, 8000),
(8, 3, 1, 8500), (8, 2, 1, 6000);

INSERT INTO pago (pedido_id, metodo, monto, fecha) VALUES
(1, 'efectivo', 24000, NOW() - INTERVAL 40 DAY),
(2, 'tarjeta', 20500, NOW() - INTERVAL 30 DAY),
(3, 'transferencia', 33500, NOW() - INTERVAL 20 DAY),
(4, 'efectivo', 17500, NOW() - INTERVAL 10 DAY),
(5, 'tarjeta', 22500, NOW() - INTERVAL 3 DAY),
(6, 'efectivo', 15000, NOW() - INTERVAL 2 DAY),
(7, 'tarjeta', 26000, NOW() - INTERVAL 1 DAY);

-- blck.co · consultas SQL de la sesión 6
-- Cubre: SELECT, INSERT, UPDATE, DELETE, WHERE, ORDER BY, JOIN, COUNT, SUM, GROUP BY, HAVING
USE blckco;

-- 1. INSERT: registrar un cliente nuevo (módulo Clientes)
INSERT INTO cliente (nombre, telefono, correo) VALUES ('Julián Vargas', '3124445566', 'julian@correo.com');

-- 2. SELECT + WHERE + ORDER BY: buscar productos por nombre (buscador de Productos)
SELECT id, nombre, precio, stock FROM producto WHERE nombre LIKE '%Cap%' OR nombre LIKE '%Latte%' ORDER BY nombre ASC;

-- 3. UPDATE: descontar stock al vender (regla RN-01)
UPDATE producto SET stock = stock - 2 WHERE id = 1 AND stock >= 2;

-- 4. DELETE: borrar el cliente de prueba (siempre con WHERE)
DELETE FROM cliente WHERE correo = 'julian@correo.com';

-- 5. JOIN: pedidos con cliente y persona que atendió (listado de Pedidos)
SELECT p.id, c.nombre AS cliente, u.nombre AS atendio, p.estado, p.total, p.fecha
FROM pedido p
JOIN cliente c ON c.id = p.cliente_id
JOIN usuario u ON u.id = p.usuario_id
ORDER BY p.fecha DESC;

-- 6. JOIN múltiple: detalle de un pedido (vista Pedido #3)
SELECT pr.nombre, d.cantidad, d.precio_unitario, d.cantidad * d.precio_unitario AS subtotal
FROM detalle_pedido d JOIN producto pr ON pr.id = d.producto_id
WHERE d.pedido_id = 3;

-- 7. COUNT + SUM + GROUP BY: ingresos y pedidos por mes (reporte)
SELECT DATE_FORMAT(fecha, '%Y-%m') AS mes, COUNT(*) AS pedidos, SUM(total) AS ingresos
FROM pedido WHERE estado = 'entregado'
GROUP BY mes ORDER BY mes;

-- 8. HAVING: clientes frecuentes (3 o más pedidos entregados)
SELECT c.nombre, COUNT(*) AS pedidos, SUM(p.total) AS gastado
FROM pedido p JOIN cliente c ON c.id = p.cliente_id
WHERE p.estado = 'entregado'
GROUP BY c.id HAVING COUNT(*) >= 3 ORDER BY pedidos DESC;

-- 9. Top 5 productos más vendidos (panel)
SELECT pr.nombre, SUM(d.cantidad) AS vendidos
FROM detalle_pedido d JOIN producto pr ON pr.id = d.producto_id
GROUP BY pr.id ORDER BY vendidos DESC LIMIT 5;

-- 10. Alerta de stock bajo (panel)
SELECT nombre, stock FROM producto WHERE activo = 1 AND stock <= 10 ORDER BY stock;

-- 11. Ventas por método de pago (reporte)
SELECT metodo, COUNT(*) AS pagos, SUM(monto) AS monto FROM pago GROUP BY metodo ORDER BY monto DESC;

-- 12. Verificar que el total guardado coincide con el detalle (control de integridad, RN-03)
SELECT p.id, p.total, SUM(d.cantidad * d.precio_unitario) AS calculado
FROM pedido p JOIN detalle_pedido d ON d.pedido_id = p.id
GROUP BY p.id HAVING p.total <> calculado;

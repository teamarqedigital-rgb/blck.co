# Sustentación · preguntas probables y dónde está la respuesta

Te pedirán abrir cualquier archivo y explicarlo. Practica con esta lista.

| Pregunta | Dónde mirar | Idea clave |
|---|---|---|
| ¿Cómo llega una URL al código correcto? | `index.php` | Lee `?r=controlador/accion`, valida con regex, crea la clase y llama el método |
| ¿Por qué MVC? | `app/` | Separa datos (Model), pantalla (View) y decisiones (Controller) |
| ¿Cómo evitas inyección SQL? | `Model.php::query`, todos los modelos | Consultas preparadas con parámetros `?` |
| ¿Cómo evitas XSS? | `helpers.php::e()` y las vistas | Toda salida pasa por `htmlspecialchars` |
| ¿Qué es CSRF y cómo lo evitas? | `helpers.php::csrf_*`, `Controller::esPost` | Token por sesión en cada formulario POST |
| ¿Cómo guardas contraseñas? | `datos_prueba.sql`, `AuthController` | `password_hash` al guardar, `password_verify` al entrar |
| ¿Por qué `session_regenerate_id`? | `AuthController::login` | Evita fijación de sesión |
| ¿Cómo evitas vender sin stock? | `Pedido::crear` | Transacción con `FOR UPDATE` y verificación antes de descontar |
| ¿Por qué usas transacción? | `Pedido::crear` | Si falla un producto, se deshace todo el pedido |
| ¿Por qué el total no viene del navegador? | `Pedido::crear`, `pedido.js` | El JS solo muestra; el servidor recalcula con los precios de la BD |
| ¿Qué normalización aplicaste? | `schema.sql` | 3FN; la relación N:M pedido–producto se resuelve con `detalle_pedido` |
| ¿Por qué `precio_unitario` está en el detalle? | `detalle_pedido` | Guarda el precio del momento; si el producto cambia de precio, el historial no cambia |
| ¿Cómo diferencias roles? | `Controller::requireRol` | La sesión guarda el rol y cada acción lo verifica |
| Explica un `GROUP BY` con `HAVING` | `Reporte::clientesFrecuentes` | `HAVING` filtra grupos ya agregados; `WHERE` filtra filas |
| ¿Cómo hiciste el estilo negro? | `public/css/blckco.css` | Variables CSS sobre `data-bs-theme="dark"` de Bootstrap |

## Demostración sugerida (5 minutos)
1. Menú público → login como barista.
2. Crear pedido de 2 productos → ver el total → intentar entregar sin pagar (error) → cobrar → entregar.
3. Intentar vender más unidades que el stock (error).
4. Entrar como administrador → panel y reportes → descargar CSV.
5. Abrir `Pedido.php` y explicar la transacción línea por línea.

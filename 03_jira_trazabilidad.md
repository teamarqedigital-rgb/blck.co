la # Sesión 3 · Jira, backlog y trazabilidad

## Configuración del proyecto
1. Atlassian → crear proyecto **Scrum** llamado «Blck.co».
2. Tablero con estados: `BACKLOG · TO DO · IN PROGRESS · TESTING · DONE`.
3. Un solo Sprint de 12 sesiones. Los bugs posteriores se crean como issues tipo **Bug**.

## Épicas
Autenticación · Categorías y productos · Clientes · Pedidos · Pagos · Panel y reportes · Menú público · Diseño y documentación

## Historias con estimación (story points) y tareas
| Historia | Épica | Puntos | Prioridad | Tareas |
|---|---|---|---|---|
| HU-01 Iniciar sesión | Autenticación | 3 | Alta | Tabla usuario/rol · modelo Usuario · AuthController · vista login |
| HU-02 Gestionar categorías | Categorías y productos | 3 | Alta | Modelo · controlador · vistas · validaciones |
| HU-03 Gestionar productos | Categorías y productos | 5 | Alta | Modelo · controlador · vistas · validaciones |
| HU-04 Buscar productos | Categorías y productos | 1 | Media | Filtro LIKE · formulario de búsqueda |
| HU-05 Gestionar clientes | Clientes | 3 | Alta | Modelo · controlador · vistas |
| HU-06 Registrar pedido | Pedidos | 8 | Alta | Transacción PDO · formulario con JS · total en servidor |
| HU-07 Descontar stock | Pedidos | 3 | Alta | UPDATE con control · devolución al cancelar |
| HU-08 Cambiar estado | Pedidos | 3 | Alta | Máquina de estados · botones |
| HU-09 Registrar pago | Pagos | 3 | Alta | Tabla pago · formulario de cobro |
| HU-10 Panel | Panel y reportes | 5 | Alta | Consultas · tarjetas de cifras |
| HU-11 Reportes | Panel y reportes | 5 | Media | Consultas GROUP BY/HAVING · CSV |
| HU-12 Menú público | Menú público | 3 | Media | Vista pública · estilo blck.co |

## Matriz de trazabilidad
| Requisito | Historia | Módulo | Funcionalidad | Archivo principal | Prueba | Estado |
|---|---|---|---|---|---|---|
| RF-01 | HU-01 | Autenticación | Iniciar sesión | `AuthController.php` | CP-03, CP-05 | Hecho |
| RF-03 | HU-02 | Categorías | CRUD categorías | `CategoriaController.php` | CP-06 | Hecho |
| RF-04, RF-05 | HU-03, HU-04 | Productos | CRUD y búsqueda | `ProductoController.php` | CP-06, CP-16 | Hecho |
| RF-06 | HU-05 | Clientes | CRUD clientes | `ClienteController.php` | CP-15 | Hecho |
| RF-07, RF-08 | HU-06 | Pedidos | Registrar y calcular total | `Pedido.php::crear` | CP-07 | Hecho |
| RF-09 | HU-07 | Pedidos | Stock | `Pedido.php::crear/cancelar` | CP-08, CP-12 | Hecho |
| RF-10 | HU-08 | Pedidos | Estados | `Pedido.php::avanzar` | CP-09, CP-10, CP-11 | Hecho |
| RF-11 | HU-09 | Pagos | Cobrar | `Pedido.php::registrarPago` | CP-10 | Hecho |
| RF-13 | HU-10 | Panel | Cifras del día | `DashboardController.php` | CP-06 | Hecho |
| RF-14 | HU-11 | Reportes | Reportes y CSV | `ReporteController.php` | CP-13 | Hecho |
| RF-15 | HU-12 | Menú | Menú público | `MenuController.php` | CP-01 | Hecho |

*(Cambia «Hecho» por «Pendiente» según avances; la matriz se actualiza cada sesión.)*

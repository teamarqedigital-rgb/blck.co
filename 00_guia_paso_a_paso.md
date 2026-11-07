# Guía paso a paso · Proyecto blck.co (Cafetería #3)

Las sesiones 1 a 6 siguen tu guía del SENA. Las sesiones 7 a 12 son una propuesta de reparto: confírmala con tu instructora.
Cada sesión dura 2 horas: objetivo y Jira (10 min) → desarrollo (40) → commit (10) → pruebas (30) → documentación (20) → cierre (10).

## Parte A · Preparación
1. **XAMPP** (PHP 8+): inicia Apache y MySQL. Verifica `http://localhost` y `http://localhost/phpmyadmin`. Si Apache no arranca, cambia el puerto en `httpd.conf` (`Listen 8080`).
2. **VS Code**: extensiones PHP Intelephense, SQLTools, GitLens, Prettier. Agrega `C:\xampp\php` al PATH y comprueba `php -v`.
3. **MySQL Workbench**: conexión `local_xampp` (127.0.0.1:3306, root, sin clave). Prueba `SELECT VERSION();`.
4. **Git y GitHub**: crea el repo `adso-blckco`, luego en la carpeta del proyecto:
   ```
   git init
   git add .
   git commit -m "docs: estructura inicial del proyecto"
   git branch -M main
   git remote add origin https://github.com/TU_USUARIO/adso-blckco.git
   git push -u origin main
   ```
5. **Copia este proyecto** a `C:\xampp\htdocs\blckco`.

**Evidencias:** XAMPP en verde, `localhost/phpmyadmin`, `php -v`, `SELECT VERSION();`, primer commit.

## Instalación rápida de este proyecto
1. En phpMyAdmin, crea `blckco` si aún no existe y selecciónala antes de importar `schema.sql` y luego `datos_prueba.sql`. El esquema solo crea tablas faltantes; importa los datos de demostración una sola vez.
   ```sql
   CREATE DATABASE IF NOT EXISTS blckco CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
2. Crea el usuario de la aplicación (ejecuta en SQL y cambia la clave por una propia):
   ```sql
   CREATE USER IF NOT EXISTS 'app_blckco'@'localhost' IDENTIFIED BY 'CAMBIA_ESTA_CLAVE';
   GRANT ALL PRIVILEGES ON blckco.* TO 'app_blckco'@'localhost';
   FLUSH PRIVILEGES;
   ```
3. Copia `database.local.example.php` como `database.local.php` y configura la clave del usuario de aplicación.
4. Abre `http://localhost/blckco/` (menú público) o `http://localhost/blckco/index.php?r=auth/login`.
4. Usuarios de prueba: `admin@blck.co` / `Admin123*` · `barista@blck.co` / `Barista123*`

## Sesiones

| Sesión | Qué haces | Dónde está | Commit |
|---|---|---|---|
| 1 Investigación | Entrevista, observación y encuesta; tabular y sacar oportunidades | `docs/01_investigacion.md` | `docs: agregar investigacion` |
| 2 Requisitos | Problema, objetivos, actores, 15 RF, 7 RNF, 12 HU, 4 reglas, casos de uso | `docs/02_requisitos.md` | `docs: agregar requisitos` |
| 3 Jira | Proyecto Scrum, épicas, historias, tareas, sprint, matriz | `docs/03_jira_trazabilidad.md` | `docs: agregar trazabilidad` |
| 4 Figma | Sistema de diseño negro, 10 pantallas en 1440 y 390 px, prototipo | `docs/04_figma_design_system.md` | `docs: agregar diseno figma` |
| 5 Base de datos | EER en Workbench, 3FN, 8 tablas, datos de prueba | `schema.sql`, `datos_prueba.sql` | `feat: crear modelo de datos` |
| 6 SQL | Consultas con las 11 palabras clave | `database/consultas.sql` | `feat: agregar consultas SQL` |
| 7 PHP y MVC base | Conexión PDO, router, clases base, helpers | `index.php`, `app/core/`, `app/config/` | `feat: base mvc y conexion pdo` |
| 8 CRUD | Categorías, productos, clientes con validación en PHP y en el navegador | `app/models`, `controllers`, `views/categorias, productos, clientes` | `feat: crud de catalogos` |
| 9 Seguridad | Login, `password_verify`, sesiones, roles, CSRF | `AuthController.php`, `Controller.php`, `helpers.php` | `feat: login roles y csrf` |
| 10 Pedidos | Transacciones, reglas RN-01 a RN-04, pagos, estados, JS del total | `Pedido.php`, `PedidoController.php`, `views/pedidos`, `public/js/pedido.js` | `feat: modulo de pedidos` |
| 11 Panel y reportes | Dashboard, reportes, CSV, menú público | `Reporte.php`, `views/dashboard`, `views/reportes`, `views/menu` | `feat: panel reportes y menu` |
| 12 Pruebas y cierre | Ejecutar casos, README, ensayo de sustentación | `tests/`, `docs/05_casos_de_prueba.md`, `docs/06_sustentacion.md` | `test: casos de prueba` |

### Cómo trabajar sesiones 7 a 12 (para que el código sea tuyo)
1. **No copies el proyecto de una sola vez.** Crea los archivos en el orden de la tabla, uno por uno, escribiéndolos tú y consultando este código como referencia.
2. Después de cada archivo, abre el navegador y comprueba que funciona. Un commit por funcionalidad.
3. Antes de cada commit, lee tu código en voz alta y explica qué hace cada función. Si no puedes explicar una línea, busca qué hace o cámbiala por algo que entiendas.
4. Cambia lo que sea tuyo: nombre de la marca, textos del menú, productos, el acento de color, reglas extra. Eso es lo que hace que sea *tu* proyecto.

## Flujo de una petición (para la sustentación)
`Navegador → index.php (router) → Controller → Model (PDO) → MySQL → Model → Controller → View → Navegador`

Ejemplo, «Registrar pedido»: el navegador envía `POST ?r=pedido/crear` → `index.php` crea `PedidoController` y llama `crear()` → el controlador valida y llama `Pedido::crear()` → el modelo abre una transacción, verifica stock, inserta pedido y detalle, descuenta stock y calcula el total → el controlador redirige a `pedido/ver` → la vista muestra el detalle.

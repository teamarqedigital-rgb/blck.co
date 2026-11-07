# BLCK.OS — Fases 1 a 10

BLCK.OS es un módulo PHP independiente. No carga archivos ni modifica tablas del
sistema actual de BLCK.CO.

## Requisitos

- PHP 8.1 o superior
- Apache con `mod_rewrite` y `AllowOverride All`, o el servidor integrado de PHP
- Extensión PDO MySQL para utilizar la conexión de base de datos

## Ejecutar localmente

Desde esta carpeta:

```powershell
php -S 127.0.0.1:8081 -t public public/index.php
```

Abrir `http://127.0.0.1:8081/`. El servidor integrado no aplica `.htaccess`;
el argumento `public/index.php` actúa como enrutador.

En Apache/XAMPP, abrir la carpeta `blck-os/`. El `.htaccess` define
`public/index.php` como entrada y bloquea el acceso web a `app/`, `config/`,
`routes/`, `database/`, `tests/` y `vendor/`.

## Base de datos

La conexión es diferida: la página inicial no abre una conexión ni requiere
credenciales. Para utilizarla, configurar estas variables de entorno en PHP:

- `BLCKOS_DB_HOST` (opcional; predeterminado `127.0.0.1`)
- `BLCKOS_DB_PORT` (opcional; predeterminado `3306`)
- `BLCKOS_DB_NAME` (requerida al solicitar la conexión)
- `BLCKOS_DB_USER` (requerida al solicitar la conexión)
- `BLCKOS_DB_PASSWORD` (opcional)

`BlckOS\Core\Database::connection()` entrega una conexión PDO MySQL con
excepciones, resultados asociativos y emulación de prepares desactivada. No
crea ni modifica bases de datos o tablas.

## Fase 2: autenticación

Ejecuta manualmente `database/auth_schema.sql` dentro de una base de datos
dedicada a BLCK.OS. El esquema crea `blck_os_users` y
`blck_os_password_resets`; no modifica las tablas existentes de BLCK.CO.
Antes de ejecutar el esquema, define `BLCKOS_DB_NAME`, `BLCKOS_DB_USER` y
`BLCKOS_DB_PASSWORD` para que la aplicación se conecte a esa base.

Instala el envío SMTP aislado del sistema principal:

```powershell
composer install --no-dev --optimize-autoloader
```

Configura estas variables en el servidor PHP; no guardes contraseñas SMTP en
archivos públicos:

- `BLCKOS_PUBLIC_URL`: URL absoluta HTTPS de la instalación del módulo
- `BLCKOS_SMTP_HOST` y `BLCKOS_SMTP_PORT`
- `BLCKOS_SMTP_USERNAME` y `BLCKOS_SMTP_PASSWORD` si el servidor exige autenticación
- `BLCKOS_SMTP_ENCRYPTION`: `tls`, `ssl` o `none`
- `BLCKOS_SMTP_FROM_ADDRESS` y, opcionalmente, `BLCKOS_SMTP_FROM_NAME`
- `BLCKOS_COOKIE_SECURE=true` detrás de HTTPS si el servidor no detecta HTTPS

Se requiere HTTPS en producción. La cookie de sesión es `HttpOnly`, `SameSite=Lax`
y tiene ámbito limitado al módulo. La sesión rota al iniciar sesión y cambiar la
contraseña, expira tras 30 minutos de inactividad o 8 horas en total, y conserva
una versión de autenticación para revocar las otras sesiones tras un cambio o
restablecimiento de contraseña.

Los timestamps de autenticación y restablecimiento se almacenan y evalúan en
UTC, independientemente de la zona horaria de presentación de la aplicación.

Las contraseñas se generan con bcrypt (coste 12); se exige un mínimo de 12
caracteres y, por el límite de bcrypt, un máximo de 72 bytes. Las solicitudes
POST requieren token CSRF. Los enlaces de restablecimiento se generan con 256
bits aleatorios, se guardan como SHA-256, expiran en 60 minutos, son de un solo
uso y las solicitudes sucesivas están limitadas por una espera de 60 segundos.
La recuperación no revela si existe una cuenta para la dirección indicada. Los
fallos de envío se registran en el log PHP del servidor y el token pendiente se
revoca para permitir una nueva solicitud.

Para crear la primera cuenta, genera el hash en una consola privada:

```powershell
$securePassword = Read-Host "Contraseña inicial BLCK.OS" -AsSecureString
$plainPassword = [System.Net.NetworkCredential]::new("", $securePassword).Password
$env:BLCKOS_INITIAL_PASSWORD = $plainPassword
$hash = php -r "echo password_hash(getenv('BLCKOS_INITIAL_PASSWORD'), PASSWORD_BCRYPT, ['cost' => 12]), PHP_EOL;"
Remove-Item Env:\BLCKOS_INITIAL_PASSWORD
$plainPassword = $null
$securePassword = $null
$hash
```

No se crean cuentas por defecto. Luego de aplicar la migración de Fase 3,
crea la primera cuenta con un `INSERT` parametrizado y asígnale el rol `OWNER`:

```sql
INSERT INTO blck_os_users (email, password_hash, is_active, role_id)
VALUES (
    :email,
    :password_hash,
    1,
    (SELECT id FROM blck_os_roles WHERE code = 'OWNER')
);
```

Reemplaza los parámetros desde una herramienta segura; no guardes la contraseña
en texto plano ni en archivos del sitio.

Para asignar o cambiar el rol de una cuenta existente, usa una operación
administrativa parametrizada. Asigna el primer `OWNER` antes de otorgar acceso
administrativo a otras cuentas:

```sql
UPDATE blck_os_users
SET role_id = (SELECT id FROM blck_os_roles WHERE code = :role_code)
WHERE email = :email;
```

## Fase 3: roles y permisos

Después de ejecutar `auth_schema.sql`, aplica una sola vez
`database/phase3_roles_permissions.sql` a la misma base de datos BLCK.OS. Esta
migración crea `blck_os_roles`, `blck_os_permissions` y
`blck_os_role_permissions`, y agrega `role_id` nullable a `blck_os_users`.
No la ejecutes por segunda vez: el `ALTER TABLE` se aplica una sola vez. Las
cuentas existentes quedan sin rol hasta que un propietario las asigne; no se
promueve ninguna cuenta automáticamente.

La matriz inicial es:

| Rol | Accesos |
|---|---|
| `OWNER` | Todos los permisos, incluida la administración de roles y permisos. |
| `ADMINISTRADOR` | Usuarios, inventario, ventas, eventos, CRM, reportes y auditoría; no puede administrar roles. |
| `EMPLEADO` | Consultas y operaciones de ventas/eventos, consulta de inventario y clientes; sin administración de usuarios o permisos. |
| `CLIENTE` | Acceso al sistema, cambio de su propia contraseña y consulta de eventos. |

El middleware `PermissionMiddleware` aplica el permiso configurado por ruta en
`config/app.php`. En esta fase, `/auth/change-password` requiere
`account.password.change`; las futuras rutas de operación se conectarán en sus
fases respectivas. Un usuario inactivo o sin permiso no obtiene acceso a una
ruta protegida.

`RoleRepository::assignRole()` permite asignar un rol existente desde código
interno; la asignación de roles no se expone mediante una ruta pública en esta
fase.

## Fase 4: panel administrativo

El panel privado está disponible en `/dashboard` y requiere el permiso
`system.access`. Tras iniciar sesión, BLCK.OS redirige al panel. Los totales de
usuarios y clientes se consultan desde las tablas BLCK.OS; los indicadores de
eventos activos y ventas del día son valores de demostración y aparecen
marcados como simulados. Aún no se crean módulos ni tablas de eventos o ventas.

El panel requiere ejecutar previamente los esquemas de las fases 2 y 3,
asignar un rol habilitado al usuario y tener acceso a la base BLCK.OS.

Prueba los repositorios con:

```powershell
php tests/authentication_test.php
```

## Rutas y extensiones

Las rutas se declaran en `routes/web.php`. `Router::middleware()` permite
registrar middleware que implementa `MiddlewareInterface`; `Router::get()` y
`Router::post()` registran rutas HTTP. Las vistas se renderizan desde `app/Views`
y escapan sus valores con `BlckOS\Helpers\e()`.

## Validación de arquitectura base

1. Abrir la ruta raíz y confirmar que aparece la portada BLCK.OS.
2. Abrir `/ruta-inexistente` y confirmar la respuesta 404.
3. En Apache, intentar abrir `/app/`, `/config/` y `/routes/`; deben ser
   rechazadas.
4. No se requiere configuración o migración de base de datos para ver la portada.

## Validación de autenticación

1. Ejecuta el esquema solo en la base de datos dedicada a BLCK.OS.
2. Configura conexión PDO MySQL y el envío SMTP antes de usar recuperación.
3. Inicia sesión con una cuenta cuyo hash bcrypt esté almacenado en `blck_os_users`.
4. Comprueba el cambio de contraseña, el cierre de sesión y la invalidación de sesiones previas.
5. Solicita un restablecimiento, usa el enlace del correo y verifica que el token caduque y no pueda reutilizarse.
6. Envía un POST sin token CSRF y confirma el estado HTTP 403.
7. Usa un correo inexistente en recuperación; la respuesta pública debe ser igual a la de una cuenta existente.

## Validación de roles y permisos

1. Aplica la migración de Fase 3 después del esquema de autenticación.
2. Confirma los cuatro roles y la matriz inicial en
   `blck_os_role_permissions`.
3. Asigna un rol a cada usuario; las cuentas existentes sin `role_id` deben
   recibir explícitamente su rol.
4. Comprueba que `OWNER` tiene todos los permisos, `ADMINISTRADOR` no tiene
   `roles.manage` y que `EMPLEADO` y `CLIENTE` solo reciben los permisos de su
   matriz.
5. Ejecuta `php tests/authentication_test.php` para probar la autorización de
   rutas y el rechazo de permisos faltantes e inactivos.

## Validación del panel

1. Confirma que el usuario tiene un rol con el permiso `system.access`.
2. Inicia sesión y comprueba la redirección al panel `/dashboard`.
3. Contrasta los totales de usuarios y clientes con los datos BLCK.OS.
4. Comprueba que eventos activos y ventas del día se identifican como cifras
   simuladas, no como ventas o eventos reales.
5. Comprueba el panel en vista móvil y escritorio; debe mantenerse sin
   desbordamiento horizontal.

## Fase 5: administración de usuarios

El módulo `/users` requiere `users.view`; la creación, edición, activación,
desactivación y eliminación requieren `users.manage`. Las rutas de escritura
usan POST y protección CSRF. Solo un usuario con `roles.manage` puede asignar
roles: cuando un administrador sin ese permiso crea una cuenta, se asigna
`EMPLEADO`, y su campo de rol no puede modificarse. Las cuentas `OWNER` solo
pueden ser administradas por un `OWNER`.

El formulario de creación recibe una contraseña inicial con la política bcrypt
de BLCK.OS. Los cambios de correo o rol revocan las sesiones anteriores; la
desactivación y reactivación también incrementan `auth_version`. No se puede
desactivar ni eliminar la cuenta propia, ni retirar o eliminar el último
`OWNER` activo. La eliminación de la cuenta también elimina sus solicitudes de
restablecimiento por la clave foránea.

Esta fase no requiere otra migración: ejecuta primero
`database/auth_schema.sql` y `database/phase3_roles_permissions.sql` en la base
dedicada de BLCK.OS. Las cuentas existentes deben tener el rol apropiado
asignado desde una operación segura, como se indica en la Fase 3. No se crean
cuentas predeterminadas.

## Validación de usuarios

1. Inicia sesión con un usuario que tenga `users.view`; comprueba que puede
   consultar el listado pero que no se muestran controles de administración.
2. Con `users.manage`, crea un usuario con contraseña válida, edítalo,
   desactívalo, actívalo y elimínalo.
3. Prueba correos inválidos y duplicados, contraseñas que no coinciden y
   peticiones POST sin token CSRF.
4. Comprueba que un administrador no puede asignar roles ni gestionar una
   cuenta `OWNER`.
5. Comprueba que la propia cuenta y el último `OWNER` activo no pueden
   desactivarse o eliminarse.
6. Ejecuta `php tests/authentication_test.php` para validar CRUD, aislamiento de
   permisos, revocación de sesiones y continuidad de las fases anteriores.

## Fase 6: inventario

El módulo `/inventory` organiza categorías y subcategorías, administra productos
con SKU y precio unitario, y registra entradas, salidas y ajustes de existencias.
Los productos comienzan con stock cero; todo cambio de stock se registra en un
movimiento con su saldo anterior y posterior. Las salidas no pueden dejar
existencias negativas. La ruta de consulta requiere `inventory.view`; las
operaciones de escritura requieren `inventory.manage`.

Después de aplicar las migraciones de autenticación y roles de las fases 2 y 3,
ejecuta manualmente `database/phase6_inventory.sql` una sola vez en la base
dedicada de BLCK.OS. Esta migración depende de `blck_os_users` y no modifica las
tablas del sitio BLCK.CO. No se ejecuta automáticamente ni se debe aplicar a la
base de datos existente de BLCK.CO.

Valida el inventario con estas pruebas, que utilizan una base SQLite temporal y
no modifican la base configurada para la aplicación:

```powershell
php tests/authentication_test.php
php tests/inventory_test.php
```

La suite de inventario cubre categorías y subcategorías, SKU, coherencia entre
categorías, movimientos, saldo e historial, validación CSRF y renderizado de la
pantalla. En una instalación de desarrollo, asigna `inventory.view` y
`inventory.manage` mediante la matriz de permisos de BLCK.OS para habilitar las
acciones correspondientes.

## Fase 7: POS, ventas, comprobantes y pagos

El POS `/sales/new` registra ventas de productos con stock disponible. El
servidor vuelve a leer los precios, calcula el total y guarda el detalle de cada
producto y el método y valor del pago, además de descontar existencias dentro
de una transacción. Cada descuento queda ligado al historial de movimientos de
inventario. Si falta stock o falla una escritura, la venta, el pago y los
movimientos se revierten juntos. Una clave única de envío evita duplicar una
venta al volver a enviar la misma solicitud.

Los tickets tienen un consecutivo interno `BLCK-##########`, detalle de
productos, valores, método de pago e impresión desde el navegador. Son
comprobantes internos y **no son facturas electrónicas DIAN**. Tarjeta se guarda
como método informado; no se hacen cargos ni se integra una pasarela. El módulo
no ofrece anulación o devolución de ventas.

Después de aplicar las migraciones de las fases 2, 3 y 6 en la base dedicada de
BLCK.OS, ejecuta `database/phase7_sales.sql` una sola vez. La migración crea las
tablas de ventas, detalle y pagos, y agrega la relación opcional entre
movimientos de inventario y ventas. No se aplica automáticamente ni debe
ejecutarse en la base de BLCK.CO.

`/sales` y los tickets requieren `sales.view`; crear una venta requiere
`sales.manage`. El usuario selecciona un método de pago (efectivo, tarjeta o
transferencia); el valor registrado siempre es el total calculado en el
servidor.

Valida con:

```powershell
php tests/authentication_test.php
php tests/inventory_test.php
php tests/sales_test.php
```

La suite de ventas usa SQLite en memoria para probar cálculo de precios,
agregación de líneas repetidas, transacción, descuento y relación de stock,
permisos, CSRF y contenido imprimible del comprobante.

## Fase 8: eventos, reservas y control de ingreso

El módulo `/events` administra sesiones de DJ, Morning Beats & Coffee,
workshops y eventos privados. Los eventos pueden estar en borrador, publicados
o cancelados, y guardan sus horarios en UTC con presentación en la zona
configurada para BLCK.OS (por defecto, `America/Bogota`).

La consulta de eventos requiere `events.view`; la gestión, las reservas y el
check-in requieren `events.manage`. El rol `CLIENTE` puede consultar eventos
publicados, pero no ve borradores ni información de reservas. Las reservas se
crean únicamente desde BLCK.OS por personal autorizado; no existe registro
público ni cobro. El límite de aforo se valida dentro de una transacción.

Cada reserva confirmada tiene un código y un token aleatorio para su QR. El
código se genera localmente en el navegador con `qrcode-generator`; el check-in
requiere una solicitud POST con CSRF y solo puede completarse una vez. La cámara
se ofrece en navegadores compatibles con `BarcodeDetector`; también se puede
usar un lector QR externo o ingresar el token manualmente. El QR no constituye
un medio de pago ni una integración con BLCK.CO.

Después de aplicar las migraciones de las fases 2 y 3 en la base dedicada de
BLCK.OS, ejecuta `database/phase8_events.sql` una sola vez. Esta migración crea
las tablas `blck_os_events` y `blck_os_event_reservations`, depende de
`blck_os_users` y no se ejecuta automáticamente. No la apliques a la base de
datos existente de BLCK.CO. No se ha aplicado esta migración automáticamente.

Valida con:

```powershell
php tests/authentication_test.php
php tests/events_test.php
```

La suite de eventos usa SQLite en memoria para probar tipos y estados, fechas,
aforo, reservas, aislamiento de información, permisos, CSRF, check-in de un
solo uso y renderizado de las vistas.

## Fase 9: CRM, membresías y fidelización

El CRM está disponible en `/crm/customers`. Los perfiles tienen nombre, correo,
teléfono y estado, permiten búsqueda y conservan sus notas de historial,
membresías y movimientos de puntos al desactivarlos. Los perfiles CRM son
independientes de las cuentas de inicio de sesión BLCK.OS; no se enlazan ni
crean cuentas automáticamente.

La consulta requiere `crm.view`; crear y editar perfiles, activar o desactivar
clientes, agregar notas, asignar membresías y registrar puntos requieren
`crm.manage`. La matriz vigente concede administración CRM a `OWNER` y
`ADMINISTRADOR`; `EMPLEADO` puede consultar perfiles y `CLIENTE` no obtiene
acceso al CRM.

Las membresías tienen nombre manual y fechas; solo puede haber una asignación
activa por cliente, y una nueva conserva la anterior como inactiva. No hay
cobros ni renovación automática. El programa presenta estados programados o
vencidos según las fechas, sin borrar el historial. Los puntos se acreditan o
redimen manualmente, con motivo obligatorio y saldo protegido contra
redenciones superiores al disponible. No se conectan automáticamente con POS
ni ventas.

Después de ejecutar las migraciones de las fases 2 y 3 en la base dedicada de
BLCK.OS, aplica `database/phase9_crm.sql` una sola vez. La migración crea las
tablas `blck_os_customers`, `blck_os_customer_notes`,
`blck_os_customer_memberships` y `blck_os_customer_points`; no altera cuentas,
ventas, reservas ni tablas de BLCK.CO. No se ejecuta automáticamente ni se ha
aplicado a ninguna base.

Valida con:

```powershell
php tests/authentication_test.php
php tests/inventory_test.php
php tests/sales_test.php
php tests/events_test.php
php tests/crm_test.php
```

La suite CRM utiliza SQLite en memoria para probar gestión de perfiles,
historial, membresías, saldo de puntos, autorización, CSRF y renderizado. La
migración MySQL debe aplicarse únicamente tras verificar la base dedicada de
BLCK.OS y el orden de las fases previas.

## Fase 10: reportes

`/reports` ofrece un dashboard ejecutivo y reportes de ventas, inventario,
eventos y clientes. El período se filtra mediante fechas inclusivas, con un
máximo de 366 días; sin filtros, se consulta el período de los últimos 30 días.
Las consultas están limitadas y son de solo lectura.

Ventas, pagos, productos más vendidos, movimientos, eventos, reservas,
asistencia, clientes nuevos, membresías y puntos se calculan desde las tablas
de BLCK.OS. Las existencias y la valoración del inventario muestran el estado
actual, aunque las fechas seleccionen un período histórico. La valoración usa
el precio unitario actual del producto, no un costo histórico. Las fechas se
comparan con los timestamps registrados por la base; los horarios de eventos
se presentan en la zona horaria configurada para BLCK.OS.

La ruta requiere `reports.view`, concedido a `OWNER` y `ADMINISTRADOR` en la
matriz de permisos existente. No requiere migraciones ni escribe datos, y no
lee ni modifica la base de BLCK.CO.

Valida con:

```powershell
php tests/reports_test.php
```

La suite de reportes usa SQLite en memoria para verificar agregaciones,
límites de fecha, inventario actual, reservas y asistencia, historial de
puntos y validación de rangos. La navegación HTTP real requiere la sesión
autenticada y una base BLCK.OS que ya tenga aplicadas las migraciones previas.

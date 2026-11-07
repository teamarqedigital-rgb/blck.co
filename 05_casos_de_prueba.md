# Sesión 12 · Casos de prueba

La mayoría se ejecuta con `prueba_humo.sh` desde Git Bash en Windows. El script crea pedidos y modifica inventario, por lo que solo debe apuntar a la base aislada `blckco_test`, con el usuario `app_blckco_test`; nunca lo ejecutes contra `blckco`.

1. Crea `blckco_test`, importa `schema.sql` y `datos_prueba.sql` una sola vez, y concede al usuario `app_blckco_test` permisos únicamente sobre esa base.
2. Configura temporalmente `database.local.php` para usar `blckco_test` y `app_blckco_test`.
3. Asegúrate de que PHP, MySQL CLI y `curl` estén disponibles en Git Bash; el script también admite `PHP_BIN` y `MYSQL_BIN` para especificar sus rutas.
4. Autoriza explícitamente las pruebas:
   ```bash
   BLCKCO_ALLOW_DB_TESTS=1 bash prueba_humo.sh http://localhost/blckco/index.php
   ```

El script se detiene si falta esa autorización o si la configuración no apunta exactamente a `blckco_test` y `app_blckco_test`. Los casos CP-16 a CP-20 se hacen a mano en el navegador.

| ID | Requisito | Caso | Resultado esperado | Tipo |
|---|---|---|---|---|
| CP-01 | RF-15 | Abrir el menú sin iniciar sesión | Se muestra el menú | Auto |
| CP-02 | RF-01 | Abrir el panel sin sesión | Redirige al login | Auto |
| CP-03 | RF-01 | Login con contraseña incorrecta | Mensaje «Correo o contraseña incorrectos» | Auto |
| CP-04 | RNF-03 | Enviar formulario sin token CSRF | Respuesta 403 | Auto |
| CP-05 | RF-01 | Login correcto | Redirige al panel | Auto |
| CP-06 | RF-03/04/06/13/14 | Abrir cada módulo con sesión | Responde 200 | Auto |
| CP-07 | RF-08, RN-03 | Pedido de 2 espressos (5.500 c/u) | Total 11.000 | Auto |
| CP-08 | RN-01 | Pedir 999 cheesecakes | Error «Stock insuficiente» | Auto |
| CP-09 | RN-04 | Entregar sin pago | El estado no cambia | Auto |
| CP-10 | RF-10/11 | Pagar y entregar | Estado «entregado» | Auto |
| CP-11 | RN-02 | Cancelar un pedido entregado | No se cancela | Auto |
| CP-12 | RF-09 | Cancelar un pedido pendiente | El stock vuelve | Auto |
| CP-13 | Roles | Barista abre reportes | Redirige al panel | Auto |
| CP-14 | Roles | Barista abre pedidos | Responde 200 | Auto |
| CP-15 | RNF-03 | Cliente llamado `<script>` | Se muestra como texto | Auto |
| CP-16 | RF-04 | Crear producto con precio −5 | Mensaje de validación | Manual |
| CP-17 | RNF-04 | Abrir con ancho de 390 px | Menú pasa a panel deslizante, sin scroll horizontal | Manual |
| CP-18 | RNF-06 | Navegar con Tab | Foco visible en todos los controles | Manual |
| CP-19 | RF-14 | Descargar CSV | Abre en Excel con tildes | Manual |
| CP-20 | RF-03 | Borrar categoría con productos | Mensaje de error; no se borra | Manual |
| CP-21 | Roles | Barista abre Usuarios | Se deniega el acceso a la administración de cuentas | Manual |
| CP-22 | Roles | Administrador abre Usuarios | Puede crear y administrar cuentas y roles existentes | Manual |
| CP-23 | Seguridad | Desactivar el último administrador activo | La operación se rechaza; queda un administrador activo | Manual |

Los bugs que encuentres se registran en Jira como issues tipo *Bug* con captura y pasos para reproducir.

# blck.co · cafetería digital

Sistema web full stack para gestionar pedidos, productos, clientes e inventario de una cafetería, con estética totalmente negra.
Proyecto final individual ADSO · SENA · PHP 8 + MySQL + Bootstrap 5 + JavaScript · arquitectura MVC (sin frameworks PHP).

## Módulos
Menú público · Login con roles (administrador, barista) · Administración de cuentas del equipo · Categorías · Productos · Clientes · Pedidos con pagos y estados · Panel · Reportes con CSV

## Instalación (XAMPP)
1. Coloca esta carpeta en `C:\xampp\htdocs\blckco` y en el panel de XAMPP inicia **Apache** y **MySQL**.
2. En phpMyAdmin, selecciona **SQL** y crea la base si todavía no existe:
   ```sql
   CREATE DATABASE IF NOT EXISTS blckco CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
   Después selecciona `blckco`, importa `schema.sql` y luego `datos_prueba.sql`. El esquema solo crea tablas faltantes: no borra la base ni los datos existentes. Importa los datos de demostración una sola vez.
3. En phpMyAdmin, abre la pestaña **SQL** y crea el usuario de la aplicación:
   ```sql
   CREATE USER IF NOT EXISTS 'app_blckco'@'localhost' IDENTIFIED BY 'ClaveSegura123';
   GRANT ALL PRIVILEGES ON blckco.* TO 'app_blckco'@'localhost';
   FLUSH PRIVILEGES;
   ```
4. Abre `http://localhost/blckco/` (o `http://localhost/blckco/index.php`).

Si tu MySQL usa otras credenciales, copia `database.local.example.php` como `database.local.php` y edita el usuario y la contraseña. También puedes cambiar el resto de la conexión en ese archivo. `database.local.php` está excluido de Git.

Usuarios de prueba: `admin@blck.co` / `Admin123*` · `barista@blck.co` / `Barista123*`
(cámbialos antes de publicar el proyecto en cualquier servidor).

## Archivos principales
```
index.php          Punto de entrada y router
*Controller.php    Controladores
*.php              Modelos, layouts y vistas
config.php         Configuración de conexión y aplicación
schema.sql         Esquema de la base de datos
datos_prueba.sql   Datos y usuarios de demostración
blckco.css         Estilos
app.js, pedido.js  JavaScript
```

## Reglas de negocio
RN-01 no se vende sin stock · RN-02 un pedido entregado o cancelado no se modifica · RN-03 el total se calcula en el servidor · RN-04 no se entrega sin pago

## Seguridad
Consultas preparadas PDO · `password_hash` · escape de salida · token CSRF · cookies de sesión `HttpOnly`/`SameSite` · control de acceso por rol · `database.local.php` fuera de Git

## Usuarios y roles
Solo el rol `administrador` puede abrir **Usuarios**, crear cuentas, cambiar contraseñas y cambiar el rol o estado de otras cuentas. El rol `barista` conserva el acceso operativo actual y no puede acceder a la administración de usuarios, categorías ni reportes. Las cuentas se desactivan en vez de eliminarse para conservar las referencias de pedidos. El sistema protege contra la desactivación o degradación del último administrador activo y no permite cambiar el rol ni desactivar la cuenta con la que se inició sesión.

Las contraseñas nuevas deben tener entre 12 y 72 bytes y se guardan mediante `password_hash`. No se añadió ni modificó ninguna tabla: la gestión usa las tablas `usuario` y `rol` existentes.

## Pruebas
Las pruebas de humo crean pedidos y modifican stock. Ejecútalas únicamente en una base desechable `blckco_test`, nunca contra `blckco`.

1. Crea `blckco_test` en MySQL y asígnale su propio usuario, limitado a esa base:
   ```sql
   CREATE DATABASE IF NOT EXISTS blckco_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER IF NOT EXISTS 'app_blckco_test'@'localhost' IDENTIFIED BY 'CAMBIA_ESTA_CLAVE';
   GRANT ALL PRIVILEGES ON blckco_test.* TO 'app_blckco_test'@'localhost';
   ```
2. Selecciona `blckco_test` e importa `schema.sql` y `datos_prueba.sql`.
3. En `database.local.php`, configura `name` como `blckco_test`, `user` como `app_blckco_test` y la clave que configuraste. Esa cuenta no debe tener permisos sobre `blckco`.
4. Con el servidor apuntando a esa configuración de pruebas, ejecútalas explícitamente:
   ```bash
   BLCKCO_ALLOW_DB_TESTS=1 bash prueba_humo.sh http://localhost/blckco/index.php
   ```

El script se detiene si no se autoriza explícitamente o si la configuración no apunta exactamente a `blckco_test` con el usuario `app_blckco_test`. Si MySQL root requiere una clave, indica un archivo de opciones local mediante `MYSQL_DEFAULTS_FILE`; no pongas contraseñas en los comandos. Para comenzar desde una base de pruebas limpia, elimina y vuelve a crear únicamente `blckco_test` antes de importar el esquema y los datos. **Nunca uses esa operación con `blckco`.**

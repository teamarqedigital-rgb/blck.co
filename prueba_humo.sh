#!/bin/bash
# Pruebas de humo: ejecuta mutaciones solo contra la base aislada blckco_test.
# Uso: BLCKCO_ALLOW_DB_TESTS=1 bash prueba_humo.sh http://localhost/blckco/index.php
set -u

ROOT=$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)
B=${1:-http://localhost/blckco/index.php}
TEST_DB=blckco_test
TEST_DB_USER=app_blckco_test
PHP_BIN=${PHP_BIN:-php}
MYSQL_BIN=${MYSQL_BIN:-mysql}

if [[ ${BLCKCO_ALLOW_DB_TESTS:-} != 1 ]]; then
  echo "Pruebas canceladas: para autorizar mutaciones en la base aislada, define BLCKCO_ALLOW_DB_TESTS=1." >&2
  exit 2
fi

if ! command -v "$PHP_BIN" >/dev/null 2>&1 || ! command -v "$MYSQL_BIN" >/dev/null 2>&1 || ! command -v curl >/dev/null 2>&1; then
  echo "Se requieren PHP, MySQL CLI y curl en PATH." >&2
  exit 2
fi

APP_DB=$("$PHP_BIN" -r 'require $argv[1]; echo DB_CONFIG["name"];' "$ROOT/config.php") || exit 2
APP_DB_USER=$("$PHP_BIN" -r 'require $argv[1]; echo DB_CONFIG["user"];' "$ROOT/config.php") || exit 2
if [[ $APP_DB != "$TEST_DB" || $APP_DB_USER != "$TEST_DB_USER" ]]; then
  echo "Pruebas canceladas: config.php debe apuntar exactamente a $TEST_DB con el usuario $TEST_DB_USER." >&2
  echo "La base configurada es '$APP_DB' y el usuario '$APP_DB_USER'; no se hicieron solicitudes HTTP ni cambios." >&2
  exit 2
fi

mysql_test() {
  if [[ -n ${MYSQL_DEFAULTS_FILE:-} ]]; then
    "$MYSQL_BIN" "--defaults-extra-file=$MYSQL_DEFAULTS_FILE" -u root "$@"
  else
    "$MYSQL_BIN" -u root "$@"
  fi
}

if ! mysql_test "$TEST_DB" -N -e "SELECT 1 FROM usuario WHERE correo='admin@blck.co' AND activo=1 LIMIT 1" >/dev/null 2>&1; then
  echo "No se puede leer $TEST_DB. Créala, impórtale schema.sql y datos_prueba.sql y concede acceso al usuario de pruebas." >&2
  exit 2
fi

TMP=$(mktemp -d) || exit 2
trap 'rm -rf "$TMP"' EXIT
J="$TMP/admin.cookies"
OUT="$TMP/response.html"
ok=0
fail=0

code() {
  curl -sS -o "$OUT" -w "%{http_code}" -b "$J" -c "$J" "$@"
}

chk() {
  if [[ $2 == "$3" ]]; then
    echo "OK   $1"
    ok=$((ok + 1))
  else
    echo "FALLA $1 (esperado $3, obtuvo $2)"
    fail=$((fail + 1))
  fi
}

has() {
  if grep -q "$2" "$OUT"; then
    chk "$1" si si
  else
    chk "$1" no si
  fi
}

tok() {
  curl -sS -b "$J" -c "$J" "$B?r=$1" |
    grep -o 'name="csrf" value="[a-f0-9]*"' |
    head -1 |
    sed 's/.*value="//;s/"//'
}

chk "CP-01 menú público sin sesión" "$(code "$B?r=menu/index")" 200
chk "CP-02 panel sin sesión redirige" "$(code "$B?r=dashboard/index")" 302
T=$(tok auth/login)
code -d "csrf=$T&correo=admin@blck.co&clave=mala" "$B?r=auth/login" >/dev/null
has "CP-03 login con clave mala muestra error" incorrectos
chk "CP-04 POST sin token CSRF es rechazado" "$(code -d 'correo=admin@blck.co&clave=Admin123*' "$B?r=auth/login")" 403
chk "CP-05 login correcto redirige" "$(code -d "csrf=$T&correo=admin@blck.co&clave=Admin123*" "$B?r=auth/login")" 302
for r in dashboard/index pedido/index producto/index categoria/index cliente/index reporte/index; do
  chk "CP-06 abre $r" "$(code "$B?r=$r")" 200
done

# Crear pedido (cliente 1, producto 1 x2 = 2 x 5500).
T=$(tok pedido/crear)
code -d "csrf=$T&cliente_id=1&cantidad[1]=2" "$B?r=pedido/crear" >/dev/null
ID=$(mysql_test "$TEST_DB" -N -e "SELECT MAX(id) FROM pedido")
TOTAL=$(mysql_test "$TEST_DB" -N -e "SELECT total FROM pedido WHERE id=$ID")
chk "CP-07 total calculado en servidor" "$TOTAL" "11000.00"

# RN-01: más unidades que el stock.
T=$(tok pedido/crear)
code -d "csrf=$T&cliente_id=1&cantidad[10]=999" "$B?r=pedido/crear" >/dev/null
has "CP-08 RN-01 bloquea venta sin stock" "Stock insuficiente"

# RN-04: no entregar sin pago.
T=$(tok "pedido/ver&id=$ID")
code -d "csrf=$T&id=$ID" "$B?r=pedido/avanzar" >/dev/null
code -d "csrf=$T&id=$ID" "$B?r=pedido/avanzar" >/dev/null
chk "CP-09 RN-04 no entrega sin pago" "$(mysql_test "$TEST_DB" -N -e "SELECT estado FROM pedido WHERE id=$ID")" preparando
code -d "csrf=$T&id=$ID&metodo=efectivo" "$B?r=pedido/pagar" >/dev/null
code -d "csrf=$T&id=$ID" "$B?r=pedido/avanzar" >/dev/null
chk "CP-10 entrega tras pagar" "$(mysql_test "$TEST_DB" -N -e "SELECT estado FROM pedido WHERE id=$ID")" entregado

# RN-02: pedido entregado no se modifica.
code -d "csrf=$T&id=$ID" "$B?r=pedido/cancelar" >/dev/null
chk "CP-11 RN-02 entregado no se cancela" "$(mysql_test "$TEST_DB" -N -e "SELECT estado FROM pedido WHERE id=$ID")" entregado

# Cancelar devuelve el stock.
S0=$(mysql_test "$TEST_DB" -N -e "SELECT stock FROM producto WHERE id=2")
T=$(tok pedido/crear)
code -d "csrf=$T&cliente_id=2&cantidad[2]=3" "$B?r=pedido/crear" >/dev/null
ID2=$(mysql_test "$TEST_DB" -N -e "SELECT MAX(id) FROM pedido")
T=$(tok "pedido/ver&id=$ID2")
code -d "csrf=$T&id=$ID2" "$B?r=pedido/cancelar" >/dev/null
chk "CP-12 cancelar devuelve stock" "$(mysql_test "$TEST_DB" -N -e "SELECT stock FROM producto WHERE id=2")" "$S0"

# Un usuario barista no accede a reportes.
J="$TMP/barista.cookies"
T=$(tok auth/login)
code -d "csrf=$T&correo=barista@blck.co&clave=Barista123*" "$B?r=auth/login" >/dev/null
chk "CP-13 barista no accede a reportes" "$(code "$B?r=reporte/index")" 302
chk "CP-14 barista sí ve pedidos" "$(code "$B?r=pedido/index")" 200
code "$B?r=auth/logout" >/dev/null

# XSS: el nombre se escapa.
J="$TMP/admin.cookies"
T=$(tok cliente/crear)
code -d "csrf=$T&nombre=<script>alert(1)</script>&telefono=&correo=" "$B?r=cliente/crear" >/dev/null
code "$B?r=cliente/index" >/dev/null
if grep -q '<script>alert(1)' "$OUT"; then r=si; else r=no; fi
chk "CP-15 XSS escapado en listado" "$r" no
mysql_test "$TEST_DB" -e "DELETE FROM cliente WHERE nombre LIKE '<script>%'"

echo "Resultado: $ok correctas, $fail con falla (base aislada: $TEST_DB)"
[[ $fail -eq 0 ]]

#!/bin/bash
# Humo: entra con cada cuenta, abre sus pantallas y sale. Marca todo lo que no sea 200/redirección esperada o muestre un error.
# Uso: BASE=https://gaining-grades.onrender.com PASS='clave' scripts/smoke.sh correo1 correo2 ...   (el super admin lleva su clave: PASS_ADMIN)
BASE=${BASE:?define BASE}; fail=0
check() { # etiqueta, código, archivo
  if [[ "$2" != 200 ]] || grep -qiE 'Server Error|Whoops|Exception|Undefined|SQLSTATE|<title>.*(500|419|403|404)' "$3"; then echo "  FALLA $1 -> $2"; fail=1; else echo "  ok    $1"; fi
}
for email in "$@"; do
  echo "== $email"; jar=$(mktemp); out=$(mktemp)
  token() { grep -o 'name="_token" value="[^"]*"' "$1" | head -1 | sed 's/.*value="//;s/"//'; }
  curl -s -c "$jar" -b "$jar" "$BASE/login" -o "$out"; t=$(token "$out")
  pass=$PASS; [[ "$email" == "${ADMIN_EMAIL:-}" ]] && pass=$PASS_ADMIN
  code=$(curl -s -L -c "$jar" -b "$jar" -o "$out" -w '%{http_code}' --data-urlencode "_token=$t" --data-urlencode "email=$email" --data-urlencode "password=$pass" "$BASE/login")
  check "login -> inicio" "$code" "$out"
  for path in /perfil /cursos /catalogo; do
    code=$(curl -s -L -c "$jar" -b "$jar" -o "$out" -w '%{http_code}' "$BASE$path")
    [[ "$path" == /cursos && "$code" == 403 ]] && { echo "  ok    $path (403 esperado para alumno)"; continue; }
    check "$path" "$code" "$out"
  done
  t=$(token "$out"); curl -s -c "$jar" -b "$jar" -o "$out" "$BASE/perfil"; t=$(token "$out")
  code=$(curl -s -L -c "$jar" -b "$jar" -o "$out" -w '%{http_code}' --data-urlencode "_token=$t" "$BASE/logout")
  check "logout -> portada" "$code" "$out"
  code=$(curl -s -o /dev/null -b "$jar" -w '%{http_code}' "$BASE/dashboard"); [[ "$code" == 302 ]] && echo "  ok    sesión cerrada" || { echo "  FALLA sigue con sesión ($code)"; fail=1; }
  rm -f "$jar" "$out"
done
exit $fail

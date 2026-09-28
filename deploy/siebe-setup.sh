#!/usr/bin/env bash
# Monta Siebe Residences en el servidor desde cero: contenedores, base,
# migraciones, catálogo de 18 unidades y sus imágenes.
#
#   bash deploy/siebe-setup.sh            # muestra qué haría
#   bash deploy/siebe-setup.sh --apply    # lo hace
#
# Se corre desde /opt/siebe después de clonar la rama `siebe`. Es idempotente:
# volver a correrlo no duplica unidades ni imágenes (units:import actualiza por
# nombre y units:shared-images sólo agrega lo que falta).
#
# Requisitos previos, fuera de este script:
#   · DNS de siebe.urbatrix.com apuntando al servidor.
#   · .env con APP_KEY, DB_PASSWORD y los datos de empresa (ver .env.siebe).
set -euo pipefail

cd "$(dirname "$0")/.."
ROOT=$(pwd)

branch=$(git rev-parse --abbrev-ref HEAD)
if [[ "$branch" != "siebe" ]]; then
  echo "✗ Este checkout está en la rama «$branch», no en siebe. No hago nada." >&2
  exit 1
fi

if [[ ! -f .env ]]; then
  echo "✗ Falta .env. Copiá .env.siebe y completá APP_KEY y DB_PASSWORD." >&2
  exit 1
fi

DRY=1
[[ "${1:-}" == "--apply" ]] && DRY=0

run() {
  if [[ $DRY -eq 1 ]]; then
    echo "   [dry-run] $*"
  else
    echo "   ▸ $*"
    "$@"
  fi
}

echo "▸ $ROOT (rama $branch · $(git log --oneline -1))"
echo
echo "1· Contenedores"
run docker compose up -d --build

echo
echo "2· Esperar a que MySQL acepte conexiones"
if [[ $DRY -eq 0 ]]; then
  for i in $(seq 1 60); do
    if docker exec siebe_mysql mysqladmin ping --silent >/dev/null 2>&1; then
      echo "   ▸ MySQL listo (${i}s)"; break
    fi
    sleep 1
    [[ $i -eq 60 ]] && { echo "✗ MySQL no levantó en 60s" >&2; exit 1; }
  done
else
  echo "   [dry-run] esperaría a siebe_mysql"
fi

echo
echo "3· Dependencias PHP"
# El compose monta el repo sobre /var/www, lo que tapa el vendor/ que quedó en
# la imagen. Como vendor/ no está versionado, hay que instalarlo dentro del
# contenedor contra el directorio montado o artisan no arranca.
if [[ $DRY -eq 0 ]] && [[ ! -f vendor/autoload.php ]]; then
  run docker exec siebe_app composer install --no-dev --optimize-autoloader --no-interaction
else
  [[ $DRY -eq 1 ]] && echo "   [dry-run] composer install si falta vendor/"
fi

# php-fpm corre como www-data; el checkout es de root, así que sin esto
# Laravel no puede escribir logs ni caché y todo responde 500.
run docker exec siebe_app chown -R www-data:www-data storage bootstrap/cache

echo
echo "4· Aplicación"
run docker exec siebe_app php artisan migrate --force
run docker exec siebe_app php artisan db:seed --class=AdminUserSeeder --force
run docker exec siebe_app php artisan db:seed --class=CrmOperativoSeeder --force

echo
echo "5· Catálogo: 18 unidades desde la lista de precios del estudio"
run docker exec siebe_app php artisan units:import --public --force

echo
echo "6· Galería compartida (renders del estudio)"
run docker exec siebe_app php artisan units:shared-images

echo
echo "7· Cachés"
run docker exec siebe_app php artisan storage:link
run docker exec siebe_app php artisan config:clear
run docker exec siebe_app php artisan view:clear

echo
if [[ $DRY -eq 1 ]]; then
  echo "Esto fue una simulación. Para aplicar:  bash deploy/siebe-setup.sh --apply"
else
  echo "✓ Listo. https://siebe.urbatrix.com"
  echo "  phpMyAdmin en el puerto 8083 del servidor."
fi

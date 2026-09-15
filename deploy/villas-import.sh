#!/usr/bin/env bash
# Despliegue de las villas de Bahía Mar en producción.
#
#   bash deploy/villas-import.sh            # sólo muestra qué haría (dry-run)
#   bash deploy/villas-import.sh --apply    # respalda la base e importa
#
# Se corre desde la raíz del proyecto en el servidor, después de un git pull.
# Se niega a correr fuera de la rama landmass.
#
# En producción la app vive en Docker (landmass_app / landmass_mysql), así que
# artisan y mysqldump se ejecutan dentro de los contenedores. Si no hay Docker,
# usa los binarios del host.
set -euo pipefail

cd "$(dirname "$0")/.."
ROOT=$(pwd)
NAME=$(basename "$ROOT")            # landmass → landmass_app, landmass_mysql

branch=$(git rev-parse --abbrev-ref HEAD)
if [[ "$branch" != "landmass" ]]; then
  echo "✗ Este proyecto está en la rama «$branch», no en landmass. No hago nada." >&2
  exit 1
fi

if command -v docker >/dev/null && docker ps --format '{{.Names}}' | grep -qx "${NAME}_app"; then
  ARTISAN=(docker exec "${NAME}_app" php artisan)
  DUMP=(docker exec "${NAME}_mysql" sh -c)
  MODE="docker (${NAME}_app)"
else
  ARTISAN=(php artisan)
  DUMP=(sh -c)
  MODE="host"
fi

echo "▸ $ROOT  (rama $branch · $(git log --oneline -1) · $MODE)"
echo

if [[ "${1:-}" != "--apply" ]]; then
  "${ARTISAN[@]}" villas:import --dry-run
  echo
  echo "Esto fue una simulación. Para aplicar:  bash deploy/villas-import.sh --apply"
  exit 0
fi

# ── respaldo de la base antes de tocar nada ──────────────────────────────────
envval(){ grep -E "^$1=" .env | head -1 | cut -d= -f2- | tr -d '"' | tr -d "'"; }
DB=$(envval DB_DATABASE); DBU=$(envval DB_USERNAME); DBP=$(envval DB_PASSWORD)
mkdir -p storage/app/db-backups
BK="storage/app/db-backups/pre-villas-$(date +%Y%m%d-%H%M%S).sql.gz"
if "${DUMP[@]}" "command -v mysqldump >/dev/null"; then
  "${DUMP[@]}" "MYSQL_PWD='$DBP' mysqldump -u '$DBU' --single-transaction --quick '$DB'" | gzip > "$BK"
  echo "▸ respaldo: $BK ($(du -h "$BK" | cut -f1))"
else
  echo "⚠ no hay mysqldump; sigo sin respaldo de la base" >&2
fi
echo

# ── importación ──────────────────────────────────────────────────────────────
"${ARTISAN[@]}" villas:import --force
"${ARTISAN[@]}" view:clear >/dev/null
"${ARTISAN[@]}" cache:clear >/dev/null
echo
echo "✓ Listo. Para revertir la base:  gunzip < $BK | docker exec -i ${NAME}_mysql mysql -u $DBU -p $DB"

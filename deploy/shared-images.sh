#!/usr/bin/env bash
# Cuelga las imágenes compartidas (áreas comunes + renders genéricos) en todas
# las unidades de Makai en producción.
#
#   bash deploy/shared-images.sh            # sólo muestra qué haría (dry-run)
#   bash deploy/shared-images.sh --apply    # respalda la base y agrega las imágenes
#   bash deploy/shared-images.sh --remove   # las quita (las subidas a mano quedan)
#
# Se corre desde la raíz del proyecto en el servidor, después de un git pull.
# Se niega a correr fuera de la rama makai.
#
# Si la app vive en Docker ({carpeta}_app / {carpeta}_mysql), artisan y
# mysqldump se ejecutan dentro de los contenedores; si no, en el host.
set -euo pipefail

cd "$(dirname "$0")/.."
ROOT=$(pwd)
NAME=${APP_CONTAINER_PREFIX:-$(basename "$ROOT")}

branch=$(git rev-parse --abbrev-ref HEAD)
if [[ "$branch" != "makai" ]]; then
  echo "✗ Este proyecto está en la rama «$branch», no en makai. No hago nada." >&2
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

case "${1:-}" in
  --apply)  ARGS=() ;;
  --remove) ARGS=(--remove) ;;
  *)
    "${ARTISAN[@]}" units:shared-images --dry-run
    echo
    echo "Esto fue una simulación. Para aplicar:  bash deploy/shared-images.sh --apply"
    exit 0 ;;
esac

# ── respaldo de la base antes de tocar nada ──────────────────────────────────
envval(){ grep -E "^$1=" .env | head -1 | cut -d= -f2- | tr -d '"' | tr -d "'"; }
DB=$(envval DB_DATABASE); DBU=$(envval DB_USERNAME); DBP=$(envval DB_PASSWORD)
mkdir -p storage/app/db-backups
BK="storage/app/db-backups/pre-shared-images-$(date +%Y%m%d-%H%M%S).sql.gz"
if "${DUMP[@]}" "command -v mysqldump >/dev/null"; then
  "${DUMP[@]}" "MYSQL_PWD='$DBP' mysqldump -u '$DBU' --single-transaction --quick '$DB'" | gzip > "$BK"
  echo "▸ respaldo: $BK ($(du -h "$BK" | cut -f1))"
else
  echo "⚠ no hay mysqldump; sigo sin respaldo de la base" >&2
fi
echo

"${ARTISAN[@]}" units:shared-images "${ARGS[@]}"
"${ARTISAN[@]}" view:clear >/dev/null

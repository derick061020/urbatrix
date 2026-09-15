#!/usr/bin/env bash
# Despliegue de las villas de Bahía Mar en producción.
#
#   bash deploy/villas-import.sh            # sólo muestra qué haría (dry-run)
#   bash deploy/villas-import.sh --apply    # respalda la base e importa
#
# Pensado para correrse desde la raíz del proyecto en el servidor, después de
# un git pull. Se niega a correr fuera de la rama landmass.
set -euo pipefail

cd "$(dirname "$0")/.."
ROOT=$(pwd)

branch=$(git rev-parse --abbrev-ref HEAD)
if [[ "$branch" != "landmass" ]]; then
  echo "✗ Este proyecto está en la rama «$branch», no en landmass. No hago nada." >&2
  exit 1
fi

echo "▸ $ROOT  (rama $branch · $(git log --oneline -1))"
echo

if [[ "${1:-}" != "--apply" ]]; then
  php artisan villas:import --dry-run
  echo
  echo "Esto fue una simulación. Para aplicar:  bash deploy/villas-import.sh --apply"
  exit 0
fi

# ── respaldo de la base antes de tocar nada ──────────────────────────────────
envval(){ grep -E "^$1=" .env | head -1 | cut -d= -f2- | tr -d '"' | tr -d "'"; }
DB=$(envval DB_DATABASE); DBU=$(envval DB_USERNAME); DBP=$(envval DB_PASSWORD)
DBH=$(envval DB_HOST); DBH=${DBH:-127.0.0.1}
mkdir -p storage/app/db-backups
BK="storage/app/db-backups/pre-villas-$(date +%Y%m%d-%H%M%S).sql.gz"
if command -v mysqldump >/dev/null; then
  MYSQL_PWD="$DBP" mysqldump -h "$DBH" -u "$DBU" --single-transaction --quick "$DB" | gzip > "$BK"
  echo "▸ respaldo: $BK ($(du -h "$BK" | cut -f1))"
else
  echo "⚠ no hay mysqldump; sigo sin respaldo de la base" >&2
fi
echo

# ── importación ──────────────────────────────────────────────────────────────
php artisan villas:import --force
php artisan view:clear >/dev/null
php artisan cache:clear >/dev/null
echo
echo "✓ Listo. Si algo salió mal:  gunzip < $BK | mysql -u $DBU -p $DB"

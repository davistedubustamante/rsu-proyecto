#!/usr/bin/env bash
set -euo pipefail

mkdir -p backups
stamp="$(date -u +%Y%m%dT%H%M%SZ)"
file="backups/rsu-db-${stamp}.sql.gz"

docker compose exec -T db sh -lc 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" mysqldump -uroot --single-transaction --routines --events rsu_proyecto' \
    | gzip -c > "${file}"

test -s "${file}"
printf 'backup created: %s\n' "${file}"

#!/usr/bin/env bash
set -euo pipefail

docker compose restart app web

for attempt in {1..30}; do
    if curl --fail --silent --max-time 5 http://localhost:8080/up >/dev/null \
        && curl --fail --silent --max-time 5 http://localhost:8080/api/health >/dev/null; then
        printf 'services restarted and healthy\n'
        exit 0
    fi
    sleep 2
done

printf 'restart check failed\n' >&2
exit 1

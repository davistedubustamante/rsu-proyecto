#!/usr/bin/env bash
set -euo pipefail

base_url="${1:-http://localhost:8080}"

curl --fail --silent --show-error --max-time 10 "${base_url}/up" >/dev/null
curl --fail --silent --show-error --max-time 10 "${base_url}/api/health" >/dev/null
docker compose ps --status running --format json >/dev/null

printf 'healthy: %s\n' "${base_url}"

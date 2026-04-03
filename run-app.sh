#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT_DIR"

APP_ENV_FILE="${APP_ENV_FILE:-.env.production}"
SECRET_ENV_FILE="${SECRET_ENV_FILE:-deploy/env/secret.env}"
COMPOSE_FILE="${COMPOSE_FILE:-docker-compose-prod.yaml}"

if [[ ! -f "$APP_ENV_FILE" ]]; then
    echo "Missing app env file: $APP_ENV_FILE" >&2
    echo "Create it on the server and set APP_IMAGE there." >&2
    exit 1
fi

if [[ ! -f "$SECRET_ENV_FILE" ]]; then
    echo "Missing secret env file: $SECRET_ENV_FILE" >&2
    echo "Copy deploy/env/secret.env.example to $SECRET_ENV_FILE and fill secrets there." >&2
    exit 1
fi

APP_IMAGE="$(grep -E '^APP_IMAGE=' "$APP_ENV_FILE" | tail -n 1 | cut -d '=' -f 2- || true)"

if [[ -z "$APP_IMAGE" ]]; then
    echo "APP_IMAGE is missing in $APP_ENV_FILE" >&2
    exit 1
fi

echo "Using APP_IMAGE=$APP_IMAGE"
echo "Using APP_ENV_FILE=$APP_ENV_FILE"
echo "Using SECRET_ENV_FILE=$SECRET_ENV_FILE"

docker compose \
    --env-file "$APP_ENV_FILE" \
    --env-file "$SECRET_ENV_FILE" \
    -f "$COMPOSE_FILE" \
    pull

docker compose \
    --env-file "$APP_ENV_FILE" \
    --env-file "$SECRET_ENV_FILE" \
    -f "$COMPOSE_FILE" \
    up -d --remove-orphans

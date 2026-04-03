#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT_DIR"

DEFAULT_IMAGE="${APP_IMAGE:-fujimiyashion/edugarden:latest}"
DEFAULT_REPOSITORY="${DEFAULT_IMAGE%%:*}"
IMAGE_REPOSITORY="${IMAGE_REPOSITORY:-$DEFAULT_REPOSITORY}"

if [[ -z "$IMAGE_REPOSITORY" ]]; then
    IMAGE_REPOSITORY="fujimiyashion/edugarden"
fi

GIT_DESCRIBE="$(git describe --tags --always --dirty)"
IMAGE_TAG="$(printf '%s' "$GIT_DESCRIBE" | tr '/:' '--')"

export BUILD_APP_IMAGE="${IMAGE_REPOSITORY}:${IMAGE_TAG}"

echo "BUILD_APP_IMAGE=${BUILD_APP_IMAGE}"

docker compose -f docker-compose-build.yaml build

if [[ "${1:-}" == "push" ]]; then
    docker compose -f docker-compose-build.yaml push
fi

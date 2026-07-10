#!/usr/bin/env sh
set -eu

# Stop the local Mailpit container created by start-mailpit-docker.sh.

docker rm -f zoosper-mailpit >/dev/null 2>&1 || true
printf '%s\n' 'Mailpit stopped.'

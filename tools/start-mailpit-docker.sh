#!/usr/bin/env sh
set -eu

# Start Mailpit using plain `docker run` for environments where the Docker
# Compose plugin (`docker compose`) or standalone binary (`docker-compose`) is
# not installed.
#
# This script does not handle SMTP credentials and must not be modified to print
# or store SMTP passwords, OTPs, TOTP secrets, reset tokens or recovery codes.

docker rm -f zoosper-mailpit >/dev/null 2>&1 || true

docker run -d \
  --name zoosper-mailpit \
  --restart unless-stopped \
  -p 1025:1025 \
  -p 8025:8025 \
  axllent/mailpit:latest

printf '%s\n' 'Mailpit started.'
printf '%s\n' 'SMTP: 127.0.0.1:1025'
printf '%s\n' 'UI:   http://127.0.0.1:8025'

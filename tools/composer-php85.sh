#!/usr/bin/env bash
set -euo pipefail

PHP_BIN="${PHP_BIN:-php8.5}"
COMPOSER_BIN="${COMPOSER_BIN:-$(command -v composer)}"

if ! command -v "$PHP_BIN" >/dev/null 2>&1; then
  echo "PHP 8.5 binary not found: $PHP_BIN" >&2
  exit 127
fi

if [ -z "$COMPOSER_BIN" ] || [ ! -x "$COMPOSER_BIN" ]; then
  echo "Composer binary not found. Set COMPOSER_BIN=/path/to/composer." >&2
  exit 127
fi

exec "$PHP_BIN" "$COMPOSER_BIN" "$@"

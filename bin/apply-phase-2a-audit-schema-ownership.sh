#!/usr/bin/env bash
set -euo pipefail

base_path="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
legacy_migration="${base_path}/app/zoosper-admin/database/migrations/202607090006_create_audit_login_history.php"

if [[ -f "${legacy_migration}" ]]; then
  rm -- "${legacy_migration}"
  printf 'Removed duplicate audit-table migration.\n'
else
  printf 'Duplicate audit-table migration is already absent.\n'
fi

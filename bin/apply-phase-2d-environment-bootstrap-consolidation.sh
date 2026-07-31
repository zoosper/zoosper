#!/usr/bin/env bash
set -euo pipefail

base_path="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
files=(
  "app/zoosper-core/src/Bootstrap/EnvLoader.php"
  "app/zoosper-core/src/Env/EnvFileLoader.php"
)

removed=0
for relative_path in "${files[@]}"; do
  absolute_path="${base_path}/${relative_path}"
  if [[ -f "${absolute_path}" ]]; then
    rm -- "${absolute_path}"
    printf 'Removed %s\n' "${relative_path}"
    removed=$((removed + 1))
  fi
done

printf 'Environment bootstrap consolidation complete: %d file(s) removed.\n' "${removed}"

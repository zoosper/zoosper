#!/usr/bin/env bash
set -euo pipefail

base_path="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
files=(
  "collect-and-run.sh"
  "bin/cleanup-legacy-tooling.sh"
  "bin/cleanup-old-root-tests.sh"
  "bin/pest.sh"
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

printf 'Repository tooling cleanup complete: %d file(s) removed.\n' "${removed}"

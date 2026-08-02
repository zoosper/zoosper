#!/usr/bin/env bash
set -euo pipefail
ROOT="$(pwd)"
[[ -f "$ROOT/composer.json" ]] || { echo "ERROR: run from the Zoosper repository root." >&2; exit 1; }
OUT="${1:-/tmp/zoosper-phase-7b-api-grid-hardening-source-v2.txt}"
TMP="$(mktemp)"
LIST="$(mktemp)"
trap 'rm -f "$TMP" "$LIST"' EXIT
PACKAGES=(
  packages/zoosper-grid
  packages/zoosper-admin-grid
  packages/zoosper-api-grid
  packages/zoosper-store-orders
)
for path in "${PACKAGES[@]}"; do
  [[ -d "$path" ]] || { echo "ERROR: required package missing: $path" >&2; exit 1; }
done
find "${PACKAGES[@]}" -type f \
  \( -name '*.php' -o -name '*.json' -o -name '*.md' \) \
  ! -path '*/vendor/*' ! -path '*/.git/*' -print0 | sort -z > "$LIST"
FILE_COUNT="$(tr -cd '\0' < "$LIST" | wc -c | tr -d ' ')"
[[ "$FILE_COUNT" -gt 0 ]] || { echo "ERROR: no source files discovered." >&2; exit 1; }
{
  echo "ZOOSPER PHASE 7B API GRID HARDENING SOURCE V2"
  echo "Generated: $(date --iso-8601=seconds)"
  echo "Branch: $(git branch --show-current)"
  echo "Commit: $(git rev-parse HEAD)"
  echo "Discovered files: $FILE_COUNT"
  echo "Git status:"
  git status --short
  while IFS= read -r -d '' file; do
    echo
    echo "================================================================================"
    echo "FILE: $file"
    cat "$file"
    echo
  done < "$LIST"
  echo
  echo "================================================================================"
  echo "RELIABILITY REFERENCE SEARCH"
  grep -RIn --include='*.php' --include='*.md' \
    -E 'timeout|invalid json|json_decode|status code|response size|redact|cursor|pagination|export|schema|retry|circuit|rate.?limit' \
    "${PACKAGES[@]}" 2>/dev/null || true
} > "$TMP"
[[ -s "$TMP" ]] || { echo "ERROR: source export is empty." >&2; exit 1; }
EXPORTED_COUNT="$(grep -c '^FILE: packages/' "$TMP")"
[[ "$EXPORTED_COUNT" -eq "$FILE_COUNT" ]] || { echo "ERROR: discovered $FILE_COUNT files but exported $EXPORTED_COUNT." >&2; exit 1; }
for package in "${PACKAGES[@]}"; do
  grep -q "^FILE: $package/" "$TMP" || { echo "ERROR: export has no files from $package." >&2; exit 1; }
done
BYTES="$(wc -c < "$TMP" | tr -d ' ')"
[[ "$BYTES" -ge 10000 ]] || { echo "ERROR: export is implausibly small ($BYTES bytes)." >&2; exit 1; }
install -m 0644 "$TMP" "$OUT"
echo "$OUT"

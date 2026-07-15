#!/usr/bin/env bash
#
# cleanup-legacy-tooling.sh
# -------------------------
# Phase 1.22 - Foundation Consolidation.
#
# Removes ONLY genuinely-dead, one-shot artifacts from the repo:
#   - tests/run.php                (superseded by Pest in Phase 1.21)
#   - tools/apply-*.php            (one-shot code-mods, already applied; the
#                                   deprecated churn pattern retired in 1.21)
#   - tools/add-*.php              (one-shot dependency/autoload adders, applied)
#   - tools/export-phase-*.sh      (one-shot phase export helpers)
#   - tools/phase015-fix-composer-autoload.php  (one-shot fix, applied)
#   - config/*.bak and docs/**/*.bak  (stale backups)
#
# It intentionally LEAVES ALONE:
#   - tools/verify-*.php + run-verification-suite.php  (the old crude test net -
#     retired incrementally, only as Pest replacements land; see policy doc)
#   - tools/diagnose-*.php and other operational tools  (still used at runtime)
#
# Safety: DRY RUN by default. Pass --force to actually delete.
# git history preserves everything removed here.
#
set -euo pipefail
shopt -s nullglob globstar

FORCE="${1:-}"

# Category label + list of glob patterns.
process_group() {
    local label="$1"; shift
    local patterns=("$@")
    local found=()
    local p
    for p in "${patterns[@]}"; do
        local match
        for match in $p; do
            [ -e "$match" ] && found+=("$match")
        done
    done

    if [ "${#found[@]}" -eq 0 ]; then
        return 0
    fi

    echo ""
    echo "[$label]  (${#found[@]})"
    local f
    for f in "${found[@]}"; do
        if [ "$FORCE" == "--force" ]; then
            rm -f "$f"
            echo "  removed  $f"
        else
            echo "  would remove  $f"
        fi
    done

    TOTAL=$((TOTAL + ${#found[@]}))
}

echo "=================================================================="
echo " Zoosper CMS - Phase 1.22 legacy tooling cleanup"
if [ "$FORCE" == "--force" ]; then
    echo " MODE: FORCE (deleting)"
else
    echo " MODE: DRY RUN (no changes)"
fi
echo "=================================================================="

TOTAL=0

process_group "Superseded test bootstrap" "tests/run.php"
process_group "One-shot code-mods (apply-*)" "tools/apply-*.php"
process_group "One-shot adders (add-*)" "tools/add-*.php"
process_group "One-shot phase exporters" "tools/export-phase-*.sh"
process_group "One-shot autoload fix" "tools/phase015-fix-composer-autoload.php"
process_group "Stale backups (*.bak)" "config/*.bak" "docs/**/*.bak"

echo ""
echo "------------------------------------------------------------------"
echo "NOTE: intentionally LEFT in place:"
echo "  - tools/verify-*.php and tools/run-verification-suite.php"
echo "      (old test net; retired only as Pest replacements land)"
echo "  - tools/diagnose-*.php and other operational tools (still used)"
echo "------------------------------------------------------------------"

if [ "$FORCE" == "--force" ]; then
    echo "Done. Removed ${TOTAL} file(s)."
    echo "Next: git add -A && git commit -m 'Phase 1.22: remove dead one-shot tooling'"
else
    echo "DRY RUN complete. ${TOTAL} file(s) would be removed."
    echo "Re-run to apply:  bash bin/cleanup-legacy-tooling.sh --force"
fi

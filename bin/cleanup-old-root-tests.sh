#!/usr/bin/env bash
#
# cleanup-old-root-tests.sh
# -------------------------
# Removes ONLY the superseded Phase 1.21 root-tests/ draft files that cause:
#   Pest\Exceptions\TestCaseClassOrTraitNotFound: class `Tests\TestCase` not found
#
# Co-located module tests (app/<module>/tests/...) replace the root tests/ draft.
#
# Safety: dry-run by default. Pass --force to actually delete.
# Leaves tests/run.php (pre-existing, unrelated) untouched.
#
set -euo pipefail

FORCE="${1:-}"

TARGET_FILES=(
    "tests/Pest.php"
    "tests/TestCase.php"
)
TARGET_DIRS=(
    "tests/Unit"
    "tests/Feature"
)

echo "=============================================================="
echo " Zoosper CMS - remove superseded root tests/ draft (Phase 1.21)"
echo "=============================================================="

if [ "$FORCE" != "--force" ]; then
    echo "DRY RUN (no changes). The following WOULD be removed:"
    for f in "${TARGET_FILES[@]}"; do
        [ -e "$f" ] && echo "  rm    $f"
    done
    for d in "${TARGET_DIRS[@]}"; do
        [ -e "$d" ] && echo "  rm -r $d"
    done
    echo ""
    echo "tests/run.php will be LEFT UNTOUCHED (pre-existing, unrelated)."
    echo "Re-run with --force to apply:  bash bin/cleanup-old-root-tests.sh --force"
    exit 0
fi

for f in "${TARGET_FILES[@]}"; do
    if [ -e "$f" ]; then
        rm -f "$f"
        echo "removed $f"
    fi
done
for d in "${TARGET_DIRS[@]}"; do
    if [ -e "$d" ]; then
        rm -rf "$d"
        echo "removed $d/"
    fi
done

echo ""
echo "Done. tests/run.php left untouched."
echo "Next: composer dump-autoload && ./vendor/bin/pest -c config/phpunit.xml"

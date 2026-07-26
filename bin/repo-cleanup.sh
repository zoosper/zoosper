#!/usr/bin/env bash
#
# One-shot repository cleanup for the lean-modular initiative.
# Run from the repo root on the chore/lean-modular-cleanup branch:
#
#   bash bin/repo-cleanup.sh
#   git push
#
# Removes development-journal docs, AI-session scaffolding, and ~310 one-off
# scripts in tools/, keeping only operational tooling. Uses git rm with
# --ignore-unmatch so already-removed paths never fail the run.

set -euo pipefail

if [ ! -d .git ]; then
  echo "Run this from the repository root." >&2
  exit 1
fi

echo "==> Removing development-journal docs"
git rm -r -q --ignore-unmatch \
  docs/progress \
  docs/planning \
  docs/metrics \
  docs/roadmap \
  docs/strategy \
  docs/phase-0.2-auth-database.md
git rm -q --ignore-unmatch docs/roadmap-status-fragment-*.md

echo "==> Removing AI-assistant scaffolding"
git rm -r -q --ignore-unmatch .claude
git rm -q --ignore-unmatch CLAUDE.md

echo "==> Pruning tools/ (keeping operational scripts)"
KEEP=(
  bootstrap.php
  gate.php
  sync-module-autoload.php
  verify-module-autoload-sync.php
  audit-module-package-readiness.php
  install-git-hooks.php
  publish-static-assets.php
  run-verification-suite.php
  start-mailpit-docker.sh
  stop-mailpit-docker.sh
  cleanup-expired-rate-limit-buckets.php
  reset-admin-2fa.php
  send-test-email.php
  send-2fa-setup-notification.php
  clean-admin-editor-build-artifacts.php
  clean-public-runtime-directories.php
  assert-production-database.php
)

for f in tools/*; do
  [ -f "$f" ] || continue
  base=$(basename "$f")
  keep=false
  for k in "${KEEP[@]}"; do
    if [ "$base" = "$k" ]; then keep=true; break; fi
  done
  if [ "$keep" = false ]; then
    git rm -q --ignore-unmatch "$f"
  fi
done

echo "==> Removing this script"
git rm -q --ignore-unmatch bin/repo-cleanup.sh

git commit -m "chore: remove development-journal docs, AI scaffolding, and one-off tools scripts"

echo
echo "Done. Review with 'git show --stat HEAD' then push:"
echo "  git push"

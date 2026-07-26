#!/usr/bin/env bash
set -euo pipefail

# One-time repository cleanup for Zoosper CMS.
#
# Deleting ~450 files through individual commits is impractical, so this
# script performs the removals locally in a single commit. Run it from the
# repository root on the chore/lean-modular-cleanup branch, then push:
#
#   bash bin/repo-cleanup.sh
#   git push
#
# The pull request diff will then show every deletion for review.

# --- 1. Development-journal docs (phase reports, roadmap fragments) ---
git rm -r -q --ignore-unmatch \
  docs/progress \
  docs/planning \
  docs/metrics \
  docs/roadmap \
  docs/strategy
git rm -q --ignore-unmatch docs/roadmap-status-fragment-*.md
git rm -q --ignore-unmatch docs/phase-0.2-auth-database.md

# --- 2. AI-assistant scaffolding ---
git rm -r -q --ignore-unmatch .claude
git rm -q --ignore-unmatch CLAUDE.md

# --- 3. tools/: keep operational scripts, drop one-off session artifacts ---
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
  clean-admin-editor-build-artifacts.php
  clean-public-runtime-directories.php
  assert-production-database.php
)
for f in tools/*; do
  [[ -f "$f" ]] || continue
  base=$(basename "$f")
  keep=false
  for k in "${KEEP[@]}"; do
    if [[ "$base" == "$k" ]]; then keep=true; break; fi
  done
  if [[ "$keep" != true ]]; then git rm -q "$f"; fi
done

# --- 4. This script removes itself once the cleanup is staged ---
git rm -q bin/repo-cleanup.sh

git commit -m "chore: remove development-journal docs and one-off tooling scripts"
echo "Cleanup committed. Review with 'git show --stat', then push."

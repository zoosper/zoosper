#!/usr/bin/env bash
set -euo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || true)"
[ -n "$ROOT" ] || { echo "ERROR: Run from inside a Git worktree" >&2; exit 1; }
cd "$ROOT"
[ -x .githooks/pre-push ] || chmod +x .githooks/pre-push
git config --local core.hooksPath .githooks
ACTUAL="$(git config --local --get core.hooksPath)"
[ "$ACTUAL" = ".githooks" ] || { echo "ERROR: core.hooksPath was not installed" >&2; exit 1; }
echo "Zoosper Git hooks installed from .githooks"

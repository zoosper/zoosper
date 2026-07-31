#!/usr/bin/env bash
set -euo pipefail
base_path="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
rm -rf -- "${base_path}/app/zoosper-core/src/Grid"   "${base_path}/app/zoosper-core/tests/Unit/Grid"
printf 'Retired Core-owned Grid source and tests.
'

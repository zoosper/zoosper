#!/usr/bin/env bash
set -euo pipefail
base_path="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
rm -f --   "${base_path}/app/zoosper-admin/src/Grid/GridPreferenceRepository.php"   "${base_path}/app/zoosper-admin/src/Grid/GridBookmarkRepository.php"   "${base_path}/app/zoosper-admin/database/migrations/202607310002_create_admin_grid_bookmarks.php"   "${base_path}/app/zoosper-admin/tests/Unit/Grid/GridBookmarkRepositoryTest.php"
printf 'Retired Admin-owned grid persistence files.
'

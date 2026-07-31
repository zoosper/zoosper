#!/usr/bin/env bash
set -euo pipefail

base_path="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

files=(
  "app/zoosper-two-factor/src/Service/TotpVerifier.php"
  "app/zoosper-two-factor/src/Service/TotpSecretGenerator.php"
  "app/zoosper-two-factor/src/Service/Base32.php"
  "app/zoosper-two-factor/src/Service/RecoveryCodeGenerator.php"
  "app/zoosper-two-factor/src/Service/RecoveryCodeHasher.php"
  "app/zoosper-two-factor/src/Model/AdminTwoFactorProfile.php"
  "app/zoosper-site/src/Service/SiteResolver.php"
  "app/zoosper-site/src/Context/SiteContext.php"
  "app/zoosper-core/src/Translation/Translator.php"
  "app/zoosper-core/src/Translation/ModuleTranslationLoader.php"
  "app/zoosper-admin/src/Asset/AdminAssetRenderer.php"
  "app/zoosper-admin/src/Editor/AdminEditorConfig.php"
  "app/zoosper-admin/config/editor.php"
  "themes/admin/default/templates/components/grid/page-filters.php"
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

printf 'Duplicate subsystem cleanup complete: %d file(s) removed.\n' "${removed}"

# Duplicate subsystem retirement

This cleanup removes confirmed, unreferenced alternatives after selecting the
runtime-wired implementations.

Retired surfaces include:

- the unwired `TwoFactor\Service` crypto/recovery stack and orphan profile DTO;
- the legacy site resolver and alternate site-context type;
- the unused `Core\Translation` stack in favour of `Core\I18n`;
- the unused admin asset renderer in favour of `AdminAssetTemplateRenderer`;
- orphaned editor configuration and the unsupported Tiptap mapping;
- the unreachable non-partial page-filter template.

The root editor configuration and `ContentEditorRegistry` remain authoritative.
The partial page-filter template remains at
`themes/admin/default/templates/partials/components/grid/page-filters.php`.

The deletion script is intentionally idempotent because archive extraction does
not encode file removals. A regression test prevents every retired path from
returning and confirms the canonical replacement classes remain available.

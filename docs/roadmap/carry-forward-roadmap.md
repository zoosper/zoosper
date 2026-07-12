# Carry-forward roadmap

## Completed foundations

- Admin notice success CSS restoration.
- Admin user locale persistence readiness checks.
- Admin user locale persistence.

## Coding guidelines

- Always produce clean, well-formatted code like PHPStorm Ctrl+Alt+L.
- Always include meaningful PHPDoc and helpful comments.
- Admin locale values must be normalised and strictly validated before persistence.
- Empty admin locale values should persist as null to preserve configured admin-locale fallback.
- Admin notices must retain visible success/error/warning styling after UI changes.
- Do not insert raw PHP template tags into PHP controller strings/heredocs.
- UI apply tools must not insert content inside an input attribute; prefer line-based insertion before/after complete label blocks.
- Preserve existing fields, admin sections and behaviour during refactors unless removal is explicitly requested.

## Future TODOs

- Replace hard-coded en_AU locale helper with SupportedLocaleProvider injection if/when UserAdminController receives services cleanly.
- Add per-site locale settings from SiteContext/SiteRepository.
- Add server-side block renderer integration.
- Add safe content_format=block_json switch.
- Add media library with uploads stored outside public first.
- Add pagination to admin grids.
- Add customer login and customer account management.
- Add admin menu link to mail logs
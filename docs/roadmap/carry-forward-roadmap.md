# Carry-forward roadmap

## Completed foundations

- Locale placeholder position hotfix.
- Admin notice success CSS restoration.
- Admin user locale persistence readiness checks.

## Coding guidelines

- Always produce clean, well-formatted code like PHPStorm Ctrl+Alt+L.
- Always include meaningful PHPDoc and helpful comments.
- Do not insert raw PHP template tags into PHP controller strings/heredocs.
- UI apply tools must not insert content inside an input attribute; prefer line-based insertion before/after complete label blocks.
- Verification must include rendered/source position checks for inserted UI fields.
- Admin notices must retain visible success/error/warning styling after UI changes.
- Preserve existing fields, admin sections and behaviour during refactors unless removal is explicitly requested.
- Locale codes used for translation lookup must be validated strictly before they affect file paths.

## Future TODOs

- Persist admin-user locale preference from the UI if readiness confirms the save flow is not already persisting it.
- Replace hard-coded en_AU locale helper with SupportedLocaleProvider injection if/when UserAdminController receives services cleanly.
- Add per-site locale settings from SiteContext/SiteRepository.
- Add server-side block renderer integration.
- Add safe content_format=block_json switch.
- Add media library with uploads stored outside public first.
- Add pagination to admin grids.
- Add customer login and customer account management.

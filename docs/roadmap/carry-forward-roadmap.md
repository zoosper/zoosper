# Carry-forward roadmap

## Completed foundations

- Admin user locale preference schema/resolver foundation.
- Admin user locale hydration hotfix.

## Coding guidelines

- Always produce clean, well-formatted code like PHPStorm Ctrl+Alt+L.
- Always include meaningful PHPDoc and helpful comments.
- Preserve existing fields, admin sections and behaviour during refactors unless removal is explicitly requested.
- Keep controllers clean; services and extension points should be provider/config driven where practical.
- Locale codes used for translation lookup must be validated strictly before they affect file paths.
- Apply/verify tools must use exact class-name matching when related classes share a prefix.
- Every future phase should include or update one verification runner file.

## Future TODOs

- Wire `AdminUserLocaleResolver` into admin translator resolution when an admin-user context is available.
- Add admin-user locale preference UI.
- Add per-site locale settings from SiteContext/SiteRepository.
- Add server-side block renderer integration.
- Add safe `content_format=block_json` switch.
- Add media library with uploads stored outside public first.

# Carry-forward roadmap

## Completed foundations

- Admin user locale hydration hotfix.
- Admin context translator resolution foundation.

## Coding guidelines

- Always produce clean, well-formatted code like PHPStorm Ctrl+Alt+L.
- Always include meaningful PHPDoc and helpful comments.
- Preserve existing fields, admin sections and behaviour during refactors unless removal is explicitly requested.
- Keep controllers clean; services and extension points should be provider/config driven where practical.
- Locale codes used for translation lookup must be validated strictly before they affect file paths.
- Every future phase should include or update one verification runner file.

## Future TODOs

- Add admin-user locale preference UI.
- Add per-site locale settings from SiteContext/SiteRepository.
- Add server-side block renderer integration.
- Add safe content_format=block_json switch.
- Add media library with uploads stored outside public first.
- Add pagination to admin grids.
- Add customer login and customer account management.

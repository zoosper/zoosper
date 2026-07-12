# Carry-forward roadmap

## Completed foundations

- Admin user locale preference UI foundation.
- Locale UI login controller hotfix.

## Coding guidelines

- Always produce clean, well-formatted code like PHPStorm Ctrl+Alt+L.
- Always include meaningful PHPDoc and helpful comments.
- Do not patch UI into login/auth controllers unless the phase explicitly targets login/auth UI.
- UI apply tools must target exact controller/form names, not broad "admin user" text matches.
- Preserve existing fields, admin sections and behaviour during refactors unless removal is explicitly requested.
- Locale codes used for translation lookup must be validated strictly before they affect file paths.
- Every future phase should include or update one verification runner file.

## Future TODOs

- Integrate admin-user locale preference UI into the correct UserAdminController/profile form.
- Persist admin-user locale preference from the UI if the current admin-user save flow does not already include it.
- Add per-site locale settings from SiteContext/SiteRepository.
- Add server-side block renderer integration.
- Add safe content_format=block_json switch.
- Add media library with uploads stored outside public first.
- Add pagination to admin grids.
- Add customer login and customer account management.

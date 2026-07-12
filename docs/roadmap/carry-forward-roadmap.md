# Carry-forward roadmap

## Completed foundations

- Safe UserAdminController locale UI integration.
- Heredoc detection hotfix.

## Coding guidelines

- Always produce clean, well-formatted code like PHPStorm Ctrl+Alt+L.
- Always include meaningful PHPDoc and helpful comments.
- Do not insert raw PHP template tags into PHP controller strings/heredocs.
- UI apply tools must inspect all heredoc/nowdoc opener variants, not just one assignment pattern.
- UI apply tools must be aware of the target rendering style before modifying source.
- Do not patch UI into login/auth controllers unless the phase explicitly targets login/auth UI.
- Preserve existing fields, admin sections and behaviour during refactors unless removal is explicitly requested.
- Locale codes used for translation lookup must be validated strictly before they affect file paths.
- Every future phase should include or update one verification runner file.

## Future TODOs

- Persist admin-user locale preference from the UI after safe integration.
- Replace hard-coded en_AU locale helper with SupportedLocaleProvider injection if/when UserAdminController receives services cleanly.
- Add per-site locale settings from SiteContext/SiteRepository.
- Add server-side block renderer integration.
- Add safe content_format=block_json switch.
- Add media library with uploads stored outside public first.
- Add pagination to admin grids.
- Add customer login and customer account management.

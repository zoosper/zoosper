## Admin runtime ownership

`SettingsCatalogueController` is the thin authenticated HTTP adapter. `SettingsCatalogueResponder` owns screen data and rendering, `SettingsMutationCoordinator` owns scoped save and clear workflows, and `SettingsAdminUrls` owns canonical Settings URLs.

## Presentation assets

The Settings workspace CSS and JavaScript live under `resources/assets` and are registered through `config/assets.php` and `config/admin_assets.php`. The template keeps the non-executable scope-options JSON bootstrap payload only.

## Presentation contract tests

Cross-layer Settings UI tests use the shared `settingsPresentationBundle()` fixture to inspect the semantic template, module stylesheet and deferred browser runtime as one contract without duplicating file-loading setup.

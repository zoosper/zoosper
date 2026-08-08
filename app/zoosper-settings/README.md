## Admin runtime ownership

`SettingsCatalogueController` is the thin authenticated HTTP adapter. `SettingsCatalogueResponder` owns screen data and rendering, `SettingsMutationCoordinator` owns scoped save and clear workflows, and `SettingsAdminUrls` owns canonical Settings URLs.

## Presentation assets

The Settings workspace CSS and JavaScript live under `resources/assets` and are registered through `config/assets.php` and `config/admin_assets.php`. The template keeps the non-executable scope-options JSON bootstrap payload only.

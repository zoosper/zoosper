## Admin runtime ownership

`SettingsCatalogueController` is the thin authenticated HTTP adapter. `SettingsCatalogueResponder` owns screen data and rendering, `SettingsMutationCoordinator` owns scoped save and clear workflows, and `SettingsAdminUrls` owns canonical Settings URLs.

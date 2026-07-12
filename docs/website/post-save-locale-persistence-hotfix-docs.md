# Post-save admin user locale persistence hotfix docs seed

Future documentation should explain that locale persistence is performed after the existing user save succeeds by calling `AdminUserRepository::updateLocale()`.

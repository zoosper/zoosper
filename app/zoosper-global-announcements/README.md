# zoosper/global-announcements

Zoosper_GlobalAnnouncements module for Zoosper CMS.

## Responsibilities

- Composer type: `zoosper-module`.
- `module.php` exposes module discovery metadata.
- Namespace `Zoosper\GlobalAnnouncements\` maps to `src/`.
- Provides Real-Time Global Announcement modal and authoring management for Super Admins.
- Implements `AdminAnnouncementProviderInterface` to deliver unacknowledged broadcasts to active and next-login admin users.

## Architecture

- `src/Announcement/`: Announcement domain model, lifecycle management, and SQLite/MySQL repository.
- `src/Controller/`: Announcement administrative controllers and asynchronous polling/acknowledgment endpoints.

## Configuration

- `config/admin_assets.php`: Announcement modal stylesheet and script registration.
- `config/admin_menu.php`: Admin navigation item for Announcements.
- `config/admin_routes.php`: Announcement management and polling/acknowledgment routes.
- `config/controllers.php`: Controller factories.
- `config/db_schema.php`: Declarative database schema for `admin_announcements` and `admin_announcement_acknowledgments`.
- `config/services.php`: Service container bindings for `AdminAnnouncementRepository` and `AdminAnnouncementProviderInterface`.

## Dependencies

- `php`: `^8.5`.
- `zoosper/core`: `dev-dev`.
- `zoosper/auth`: `dev-dev`.
- `zoosper/admin`: `dev-dev`.

## Testing

- Full repository suite: `zcomposer test`.
- Package suite: `php8.5 vendor/bin/pest app/zoosper-global-announcements/tests`.
- Standard quality gate: `php8.5 tools/gate.php`.

## Operational notes

- Run commands from the repository root with PHP 8.5 or the `zcomposer` wrapper.
- Keep this README current when routes, configuration manifests, dependencies, migrations, public contracts, or operational behaviour change.
- Canonical cross-module documentation remains under `docs/`; this README is the package-level technical reference.

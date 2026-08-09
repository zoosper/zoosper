# Configuration

Zoosper loads module defaults beneath project configuration. Environment variables are used for deployment-specific values and secrets.

Important groups include application identity and debug mode, database connections, sessions, Admin paths, security, Media, themes, Page cache and external service endpoints.

`config/version.php` is the central default CMS version source. `CMS_VERSION` is an optional deployment override.

Never commit `.env`, credentials, encryption keys or production connection strings.

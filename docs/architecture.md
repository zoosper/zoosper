# Zoosper architecture notes

## Current skeleton decision

This skeleton uses a tiny internal kernel/router so the project is immediately understandable and runnable. It intentionally mirrors Marko's typical project layout:

- `app/` for first-party modules
- `modules/` for third-party/community modules
- `public/index.php` as the web entry point
- `config/` for PHP configuration
- `storage/` for cache/log/session data
- `tests/` for tests

## Next Marko integration step

When ready, replace `Zoosper\Core\Http\Application` bootstrapping with real Marko bootstrapping and adapt the controller routes to Marko attributes or module route config.

Keep these architecture rules:

- API first
- security headers globally
- lean controllers
- service contracts first
- roles and permissions from day one
- no core hacks
- modules over monolith

# Auth admin Grid object graph

Phase 4R provides one Auth-owned factory for the complete Admin Users and Roles Grid
read-side object graphs. The factory composes definitions, per-admin workspace state,
narrow PDO readers, data sources and table renderers.

The factory intentionally accepts the shared `GridViewStateResolver` from the host
container so bookmark and preference repositories retain their established package
ownership. It contains no HTTP, session, CSRF, password, 2FA, role-assignment or
permission-assignment behaviour.

The next phase can register this factory and its two page builders in the existing
Auth service manifest, then prove application boot before changing live controllers.

# Module Asset Pipeline

## What

`GET /asset/{module}/{path}` serves any enabled module's declared assets
directly from `resources/assets/` — no publish step, no manual copy into
`public/`. A module declares its assets directory once via
`config/assets.php` (already existed for zoosper-admin); dropping a new
file into that directory makes it immediately servable.

## Components (all pre-existing, now wired to a live route)

- `AssetModuleRegistry` — maps module name -> absolute assets directory.
- `ModuleAssetManifestLoader` — discovers every enabled module's
  `config/assets.php` and registers it.
- `AssetResolver` — the security boundary: rejects path traversal, enforces
  a MIME-type allowlist, and confirms the resolved real path stays inside
  the module's assets directory.
- `AssetController` — turns a resolved asset into a framework-agnostic
  status/headers/body array, handling conditional GET (ETag) and setting a
  1-year immutable Cache-Control header.
- `AssetUrlGenerator` — builds `/asset/{module}/{path}` URLs.
- `AssetRouteRegistrar` (NEW) — registers the route on the Router and adapts
  `AssetController::serve()`'s array into a real `Response` via the new
  `Response::raw()` factory.

## Security model

Registered as a plain, unauthenticated route — consistent with the existing
model, where module CSS/JS served from `public/` has never been
permission-gated. `AssetResolver`'s path-traversal rejection and extension
allowlist are the actual security boundary, not authentication.

## Cache-busting

The route sets a 1-year immutable `Cache-Control` header. Callers MUST keep
using a `?v=...` query-string parameter (as `admin_assets.php` already does
for every other asset) so a content change is picked up by browsers holding
a cached copy — the route itself does not version anything.

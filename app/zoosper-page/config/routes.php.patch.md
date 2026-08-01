# Page Grid route patch

Merge these fixed routes into the current Page route manifest, preserving its
existing permission metadata and middleware format:

```php
GET  /admin/pages         -> existing Pages index action
POST /admin/pages/grid    -> Page Grid mutation action
GET  /admin/pages/export  -> Page Grid CSV export action
```

Requirements:

- the mutation route uses the existing Page-management permission and CSRF
  middleware;
- the export route uses the existing Page-view/export permission;
- neither route accepts a user ID, Grid key, filename, class or repository;
- both identities remain fixed in Page-owned services;
- mutation redirects only to `/admin/pages`;
- export response uses `GridWorkspaceExportResult::headers()` and CSV body.

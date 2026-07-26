# Media library

The media module (`packages/zoosper-media`) is the first full-stack example of a feature module using routes, ACL, schema, admin UI, and private storage.

Package-specific deep docs may also live under `packages/zoosper-media/docs/`.

## Security model

- **Originals** live outside the webroot: `storage/media/original`.
- **Public copies** are published under `public/media/` only after validation succeeds.
- Allowed types in the foundation: JPG, PNG, GIF, WebP.
- SVG, archives, documents, and executable types are rejected until dedicated policies exist.
- Server-generated paths only — user filenames are never used as filesystem paths.
- Path traversal is rejected.

## Module surfaces

```text
packages/zoosper-media/module.php
packages/zoosper-media/config/db_schema.php
packages/zoosper-media/config/services.php
packages/zoosper-media/config/controllers.php
packages/zoosper-media/config/admin_routes.php
packages/zoosper-media/config/admin_menu.php
packages/zoosper-media/config/acl.php
packages/zoosper-media/resources/views/admin/media/
```

Admin provides upload and library listing backed by the media table.

## Editor.js integration

Structured page content and Editor.js image tools wire into media uploads as permissions and endpoints stabilise. Until then, store validated JSON and use HTML fallback for public pages.

See [Sites, pages & content](sites-pages-and-content.md).

## Related guides

- [Modularity & modules](modularity-and-modules.md)
- [Schema & database](schema-and-database.md)

# Admin extension points

Modules extend Admin through route manifests, permissions, menu declarations, form configuration, grid workspaces, assets, layouts, translations and service composition.

Admin assets are module-owned and registered through `config/admin_assets.php`. Both the wrapped `assets` form and established flat declaration form are supported.

Routes must declare permissions. Stateful Admin mutations are protected by authentication and CSRF middleware. Admin controllers should delegate rendering and domain work to collaborators.

## Page revisions

The Page edit screen lists retained revisions. Administrators with `page.manage` may preview a historical snapshot or restore it through a CSRF-protected POST action. Restore captures the current Page as a safety revision before applying the selected title, slug, content, publication state, structured content and SEO metadata.

# Admin extension points

Modules extend Admin through route manifests, permissions, menu declarations, form configuration, grid workspaces, assets, layouts, translations and service composition.

Admin assets are module-owned and registered through `config/admin_assets.php`. Both the wrapped `assets` form and established flat declaration form are supported.

Routes must declare permissions. Stateful Admin mutations are protected by authentication and CSRF middleware. Admin controllers should delegate rendering and domain work to collaborators.

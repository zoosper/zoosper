# Site Theme Selection and Layouts

Phase 0.14 adds per-site theme selection and introduces theme layout/partial rendering.

## Database

`sites.theme_code` stores the selected frontend theme for each site.

## Rendering flow

```text
SiteResolver
  -> Site(themeCode)
PageRenderer
  -> render page.php into content
  -> render layout.php with content
  -> layout.php includes partials/header.php and partials/footer.php
```

## Template structure

```text
themes/default/templates/layout.php
themes/default/templates/page.php
themes/default/templates/partials/header.php
themes/default/templates/partials/footer.php
```

## Next improvement

Add admin UI for choosing a site theme and prepare module template overrides.

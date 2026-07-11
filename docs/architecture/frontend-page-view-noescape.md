# Phase 0.79 - Frontend Page View Noescape Fix

The frontend layout was already corrected, but the active page view template was a theme module override:

```text
themes/default/templates/modules/zoosper-page/page/view.php
```

That template escaped page body HTML with `$e($page->content)`, causing users to see literal tags such as `<h2>` and `<p>`.

## Rule

```text
Page title/slug: escape
Page body HTML: already sanitised before persistence, render without escaping
```
